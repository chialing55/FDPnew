<?php

namespace App\Services\Fushan;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Phase1FormalDataImporter
{
    private const CONNECTION = 'fs_seedling_exp';
    private const SOURCE = 'groundcover_phase1_cleaned';
    private const EXPERIMENT_CODE = 'groundcover_phase1';

    public function importRemaining(): array
    {
        $db = DB::connection(self::CONNECTION);
        $experiment = $db->table('experiments')->where('code', self::EXPERIMENT_CODE)->first();

        if (! $experiment) {
            throw new RuntimeException('找不到地被實驗第一期設定。');
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

        foreach ($rows as $row) {
            $location = "{$date} / {$row->plot} / {$row->individual_no}";
            $plot = trim((string) $row->plot);
            $number = trim((string) $row->individual_no);
            $species = trim((string) $row->species);
            $status = strtoupper(trim((string) $row->status));
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

            $length = $row->length === null ? null : (float) $row->length;
            $leafCount = $row->leaf_count === null ? null : (int) $row->leaf_count;
            if ($status === 'A' && ($length === null || $leafCount === null || $length === -4.0 || $leafCount === -4)) {
                $errors[] = "{$location}：A 必須有正式量測值，不能是 NULL 或 -4。";
            }
            if (in_array($status, ['D', 'G'], true) && ($length !== -4.0 || $leafCount !== -4)) {
                $errors[] = "{$location}：D／G 的長度與葉片數必須都是 -4。";
            }

            $individual = $existing[$key] ?? null;
            if ($individual && trim((string) $individual->species_name) !== $species) {
                $errors[] = "{$location}：物種由「{$individual->species_name}」變成「{$species}」。";
            }
            if ($individual && $individual->end_date !== null && $status !== 'G') {
                $errors[] = "{$location}：個體已於 {$individual->end_date} 記為 G，之後卻出現 {$status}。";
            }
            if (! $individual && $status !== 'A') {
                $errors[] = "{$location}：個體第一次出現時狀態為 {$status}，不是 A。";
            }
        }

        return array_slice($errors, 0, 50);
    }

    private function importBatch(Connection $db, int $experimentId, string $date, Collection $rows): array
    {
        $plots = $db->table('plots')
            ->where('experiment_id', $experimentId)
            ->pluck('id', 'plot_code')
            ->all();
        $individuals = $this->individualMap($db, $experimentId);
        $createdPlots = 0;
        $createdIndividuals = 0;
        $createdRecords = 0;
        $skippedAfterGone = 0;
        $now = now();

        foreach ($rows as $row) {
            $plotCode = trim((string) $row->plot);
            $number = trim((string) $row->individual_no);
            $key = $plotCode.'|'.$number;

            if (! isset($plots[$plotCode])) {
                $plots[$plotCode] = $db->table('plots')->insertGetId([
                    'experiment_id' => $experimentId,
                    'plot_code' => $plotCode,
                    'density' => 1,
                    'note' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $createdPlots++;
            }

            if (! isset($individuals[$key])) {
                $id = $db->table('individuals')->insertGetId([
                    'plot_id' => $plots[$plotCode],
                    'individual_no' => $number,
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

            $db->table('records')->insert([
                'individual_id' => $individual->id,
                'survey_date' => $date,
                'survey_period' => substr($date, 0, 7),
                'status' => $status,
                'length' => $row->length,
                'cotyledon_count' => null,
                'leaf_count' => $row->leaf_count,
                'note' => $row->note,
                'is_backfilled' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $createdRecords++;

            if ($status === 'D' && $individual->death_date === null) {
                $db->table('individuals')->where('id', $individual->id)->update([
                    'death_date' => $date,
                    'updated_at' => $now,
                ]);
                $individual->death_date = $date;
            }

            if ($status === 'D' && str_contains((string) $row->note, '補植')) {
                $db->table('individuals')->where('id', $individual->id)->update([
                    'end_date' => $date,
                    'updated_at' => $now,
                ]);
                $individual->end_date = $date;
            }

            if ($status === 'G') {
                $lastAliveDate = $db->table('records')
                    ->where('individual_id', $individual->id)
                    ->where('status', 'A')
                    ->where('survey_date', '<', $date)
                    ->max('survey_date');
                $firstDeadDate = $db->table('records')
                    ->where('individual_id', $individual->id)
                    ->where('status', 'D')
                    ->when($lastAliveDate, fn ($query) => $query->where('survey_date', '>', $lastAliveDate))
                    ->where('survey_date', '<', $date)
                    ->min('survey_date');

                $db->table('individuals')->where('id', $individual->id)->update([
                    'end_date' => $date,
                    'death_date' => $firstDeadDate ?: $date,
                    'updated_at' => $now,
                ]);
                $individual->end_date = $date;
                $individual->death_date = $firstDeadDate ?: $date;
            }
        }

        $db->table('experiments')->where('id', $experimentId)->update([
            'start_date' => $db->table(self::SOURCE)->min('survey_date'),
            'end_date' => $date,
            'is_active' => false,
            'updated_at' => $now,
        ]);

        return compact('date', 'createdPlots', 'createdIndividuals', 'createdRecords', 'skippedAfterGone');
    }

    private function individualMap(Connection $db, int $experimentId): array
    {
        return $db->table('individuals')
            ->join('plots', 'plots.id', '=', 'individuals.plot_id')
            ->where('plots.experiment_id', $experimentId)
            ->select([
                'individuals.id',
                'individuals.individual_no',
                'individuals.species_name',
                'individuals.end_date',
                'individuals.death_date',
                'plots.plot_code',
            ])
            ->get()
            ->keyBy(fn (object $row): string => $row->plot_code.'|'.$row->individual_no)
            ->all();
    }
}
