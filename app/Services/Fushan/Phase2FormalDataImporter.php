<?php

namespace App\Services\Fushan;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Phase2FormalDataImporter
{
    private const CONNECTION = 'fs_seedling_exp';

    private const SOURCE = 'groundcover_phase2_cleaned';

    private const EXPERIMENT_CODE = 'groundcover_phase2';

    /** @var array<string, true> */
    private const ALLOWED_FIRST_NON_ALIVE = [
        'GM005-DC|E308|2025-04-16|D' => true,
    ];

    public function importRemaining(): array
    {
        $db = DB::connection(self::CONNECTION);
        $experiment = $db->table('experiments')->where('code', self::EXPERIMENT_CODE)->first();

        if (! $experiment) {
            throw new RuntimeException('找不到地被實驗第二期設定。');
        }

        $lastImportedDate = $db->table('records')
            ->join('individuals', 'individuals.id', '=', 'records.individual_id')
            ->join('plots', 'plots.id', '=', 'individuals.plot_id')
            ->where('plots.experiment_id', $experiment->id)
            ->max('records.survey_date');

        $dates = $db->table(self::SOURCE)
            ->when($lastImportedDate, fn ($query) => $query->where('survey_date', '>', $lastImportedDate))
            ->distinct()
            ->orderBy('survey_date')
            ->pluck('survey_date');

        $result = ['imported' => [], 'stopped' => null, 'errors' => []];

        foreach ($dates as $date) {
            $rows = $db->table(self::SOURCE)->where('survey_date', $date)->orderBy('id')->get();
            $errors = $this->validateBatch($db, (int) $experiment->id, (string) $date, $rows);

            if ($errors !== []) {
                $result['stopped'] = (string) $date;
                $result['errors'] = $errors;

                break;
            }

            $result['imported'][] = $db->transaction(
                fn (): array => $this->importBatch($db, (int) $experiment->id, (string) $date, $rows)
            );
        }

        return $result;
    }

    private function validateBatch(Connection $db, int $experimentId, string $date, Collection $rows): array
    {
        $errors = [];
        $seen = [];
        $existing = $this->individualMap($db, $experimentId);
        $plotMap = $this->plotMap($db, $experimentId);

        foreach ($rows as $row) {
            $plot = trim((string) $row->plot);
            $number = trim((string) $row->individual_no);
            $species = trim((string) $row->species);
            $status = strtoupper(trim((string) $row->status));
            $location = "{$date} / {$plot} / {$number}";
            $key = $plot.'|'.$number;

            if ($plot === '' || $number === '' || $species === '') {
                $errors[] = "{$location}：Plot、編號或物種不可空白。";
                continue;
            }
            if (isset($seen[$key])) {
                $errors[] = "{$location}：同一次調查有重複個體。";
                continue;
            }
            $seen[$key] = true;

            if (! in_array($status, ['A', 'D', 'G'], true)) {
                $errors[] = "{$location}：Status 必須是 A、D 或 G，目前為「{$row->status}」。";
                continue;
            }

            $density = $this->numericValue($row->density);
            $surveyOrder = $this->integerValue($row->survey_order);
            if ($density === null || $density <= 0 || floor($density) !== $density) {
                $errors[] = "{$location}：density 必須是大於 0 的整數。";
            }
            if ($surveyOrder === null || $surveyOrder <= 0) {
                $errors[] = "{$location}：調查順序不可空白且必須大於 0。";
            }

            $plotRow = $plotMap[$plot] ?? null;
            if ($plotRow && (float) $plotRow->density !== $density) {
                $errors[] = "{$location}：樣區 density 由 {$plotRow->density} 變成 {$row->density}。";
            }

            $length = $this->numericValue($row->length);
            $cotyledonCount = $this->integerValue($row->cotyledon_count);
            $leafCount = $this->integerValue($row->leaf_count);
            $minusOneCount = collect([$length, $cotyledonCount, $leafCount])
                ->filter(fn ($value): bool => $value === -1 || $value === -1.0)
                ->count();

            if ($status === 'A') {
                if ($minusOneCount > 0 && $minusOneCount < 3) {
                    $errors[] = "{$location}：補登 A 的三個量測欄位必須全部為 -1。";
                } elseif ($minusOneCount === 0 && ($length === null || $cotyledonCount === null || $leafCount === null || $length === -4.0 || $cotyledonCount === -4 || $leafCount === -4)) {
                    $errors[] = "{$location}：A 必須有完整量測值，或三個量測欄位全部為 -1。";
                }
            } elseif ($length !== -4.0 || $cotyledonCount !== -4 || $leafCount !== -4) {
                $errors[] = "{$location}：D／G 的三個量測欄位必須全部為 -4。";
            }

            $individual = $existing[$key] ?? null;
            if ($individual && trim((string) $individual->species_name) !== $species) {
                $errors[] = "{$location}：物種由「{$individual->species_name}」變成「{$species}」。";
            }
            if ($individual && (int) $individual->survey_order !== $surveyOrder) {
                $errors[] = "{$location}：調查順序由 {$individual->survey_order} 變成 {$row->survey_order}。";
            }
            if ($individual && $individual->end_date !== null && $status !== 'G') {
                $errors[] = "{$location}：個體已於 {$individual->end_date} 記為 G，之後卻出現 {$status}。";
            }
            if ($individual && $individual->death_date !== null && $individual->end_date === null && $status === 'A') {
                $errors[] = "{$location}：D 後恢復 A，但前段 D 尚未整理為補登 A。";
            }
            if (! $individual && $status !== 'A' && ! isset(self::ALLOWED_FIRST_NON_ALIVE["{$key}|{$date}|{$status}"])) {
                $errors[] = "{$location}：個體第一次出現時狀態為 {$status}，且不在已確認的人工例外中。";
            }
        }

        return array_slice($errors, 0, 50);
    }

    private function importBatch(Connection $db, int $experimentId, string $date, Collection $rows): array
    {
        $plots = $this->plotMap($db, $experimentId);
        $individuals = $this->individualMap($db, $experimentId);
        $createdPlots = 0;
        $createdIndividuals = 0;
        $createdRecords = 0;
        $backfilledRecords = 0;
        $skippedAfterGone = 0;
        $now = now();

        foreach ($rows as $row) {
            $plotCode = trim((string) $row->plot);
            $number = trim((string) $row->individual_no);
            $key = $plotCode.'|'.$number;

            if (! isset($plots[$plotCode])) {
                $plotId = $db->table('plots')->insertGetId([
                    'experiment_id' => $experimentId,
                    'plot_code' => $plotCode,
                    'density' => (int) $density,
                    'note' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $plots[$plotCode] = (object) ['id' => $plotId, 'density' => (int) $density];
                $createdPlots++;
            }

            if (! isset($individuals[$key])) {
                $id = $db->table('individuals')->insertGetId([
                    'plot_id' => $plots[$plotCode]->id,
                    'individual_no' => $number,
                    'survey_order' => $row->survey_order,
                    'species_id' => null,
                    'species_name' => trim((string) $row->species),
                    'start_date' => $date,
                    'end_date' => null,
                    'death_date' => null,
                    'note' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $individuals[$key] = (object) [
                    'id' => $id,
                    'survey_order' => $row->survey_order,
                    'species_name' => trim((string) $row->species),
                    'end_date' => null,
                    'death_date' => null,
                ];
                $createdIndividuals++;
            }

            $individual = $individuals[$key];
            $status = strtoupper(trim((string) $row->status));
            if ($individual->end_date !== null && $status === 'G') {
                $skippedAfterGone++;
                continue;
            }

            $isBackfilled = $status === 'A'
                && (float) $row->length === -1.0
                && (int) $row->cotyledon_count === -1
                && (int) $row->leaf_count === -1;

            $db->table('records')->insert([
                'individual_id' => $individual->id,
                'survey_date' => $date,
                'survey_period' => substr($date, 0, 7),
                'status' => $status,
                'length' => $row->length,
                'cotyledon_count' => $row->cotyledon_count,
                'leaf_count' => $row->leaf_count,
                'note' => $row->note,
                'is_backfilled' => $isBackfilled,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $createdRecords++;
            $backfilledRecords += (int) $isBackfilled;

            if ($status === 'D' && $individual->death_date === null) {
                $db->table('individuals')->where('id', $individual->id)->update([
                    'death_date' => $date,
                    'updated_at' => $now,
                ]);
                $individual->death_date = $date;
            }

            if ($status === 'G') {
                $deathDate = $individual->death_date ?: $date;
                $db->table('individuals')->where('id', $individual->id)->update([
                    'end_date' => $date,
                    'death_date' => $deathDate,
                    'updated_at' => $now,
                ]);
                $individual->end_date = $date;
                $individual->death_date = $deathDate;
            }
        }

        $db->table('experiments')->where('id', $experimentId)->update([
            'start_date' => $db->table(self::SOURCE)->min('survey_date'),
            'end_date' => null,
            'is_active' => true,
            'updated_at' => $now,
        ]);

        return compact('date', 'createdPlots', 'createdIndividuals', 'createdRecords', 'backfilledRecords', 'skippedAfterGone');
    }

    private function plotMap(Connection $db, int $experimentId): array
    {
        return $db->table('plots')
            ->where('experiment_id', $experimentId)
            ->select(['id', 'plot_code', 'density'])
            ->get()
            ->keyBy('plot_code')
            ->all();
    }

    private function individualMap(Connection $db, int $experimentId): array
    {
        return $db->table('individuals')
            ->join('plots', 'plots.id', '=', 'individuals.plot_id')
            ->where('plots.experiment_id', $experimentId)
            ->select([
                'individuals.id',
                'individuals.individual_no',
                'individuals.survey_order',
                'individuals.species_name',
                'individuals.end_date',
                'individuals.death_date',
                'plots.plot_code',
            ])
            ->get()
            ->keyBy(fn (object $row): string => $row->plot_code.'|'.$row->individual_no)
            ->all();
    }

    private function numericValue(mixed $value): ?float
    {
        return $value === null || $value === '' || ! is_numeric($value) ? null : (float) $value;
    }

    private function integerValue(mixed $value): ?int
    {
        return $value === null || $value === '' || ! is_numeric($value) ? null : (int) $value;
    }
}
