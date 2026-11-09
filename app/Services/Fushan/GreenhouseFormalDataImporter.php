<?php

namespace App\Services\Fushan;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class GreenhouseFormalDataImporter
{
    private const CONNECTION = 'fs_seedling_exp';

    private const SOURCE = 'greenhouse_cleaned';

    private const EXPERIMENT_CODE = 'greenhouse';

    public function importRemaining(): array
    {
        $db = DB::connection(self::CONNECTION);
        $experiment = $db->table('experiments')->where('code', self::EXPERIMENT_CODE)->first();
        if ($experiment === null) {
            throw new RuntimeException('找不到溫室實驗設定。');
        }

        $lastImportedDate = $db->table('records as records')
            ->join('individuals as individuals', 'individuals.id', '=', 'records.individual_id')
            ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
            ->where('plots.experiment_id', $experiment->id)
            ->max('records.survey_date');

        $dates = $db->table(self::SOURCE)
            ->when($lastImportedDate, fn ($query) => $query->where('survey_date', '>', $lastImportedDate))
            ->distinct()->orderBy('survey_date')->pluck('survey_date');

        $result = ['imported' => [], 'stopped' => null, 'errors' => []];
        foreach ($dates as $date) {
            $rows = $db->table(self::SOURCE)->where('survey_date', $date)->orderBy('survey_order')->get();
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
        $seenIndividuals = [];
        $seenOrders = [];
        $existing = $this->individualMap($db, $experimentId);

        foreach ($rows as $row) {
            $plot = trim((string) $row->plot);
            $number = trim((string) $row->individual_no);
            $species = trim((string) $row->species);
            $shade = strtoupper(trim((string) $row->shade));
            $status = strtoupper(trim((string) $row->status));
            $order = $this->integerValue($row->survey_order);
            $length = $this->numericValue($row->length);
            $leafCount = $this->integerValue($row->leaf_count);
            $location = "{$date} / {$plot} / {$number}";
            $key = $plot.'|'.$number;

            if ($plot === '' || $number === '' || $species === '') {
                $errors[] = "{$location}：處理、編號與物種不可空白。";

                continue;
            }
            if (isset($seenIndividuals[$key])) {
                $errors[] = "{$location}：同一次調查有重複個體。";
            }
            $seenIndividuals[$key] = true;

            if ($order === null || $order <= 0) {
                $errors[] = "{$location}：調查順序必須是大於 0 的整數。";
            } elseif (isset($seenOrders[$order])) {
                $errors[] = "{$location}：調查順序 {$order} 重複。";
            }
            $seenOrders[$order] = true;

            if ($shade === '' || ! str_ends_with($plot, $shade)) {
                $errors[] = "{$location}：遮蔭處理 {$shade} 與處理代碼最後一碼不一致。";
            }
            if (! in_array($status, ['A', 'D', 'G'], true)) {
                $errors[] = "{$location}：Status 必須是 A、D 或 G。";
            } elseif ($status === 'A') {
                if ($length === null || $length < 0 || $leafCount === null || $leafCount < 0) {
                    $errors[] = "{$location}：A 必須有非負的高度與葉片數。";
                }
            } elseif ($length !== -4.0 || $leafCount !== -4) {
                $errors[] = "{$location}：D／G 的高度與葉片數必須都是 -4。";
            }

            $individual = $existing[$key] ?? null;
            if ($individual !== null && trim((string) $individual->species_name) !== $species) {
                $errors[] = "{$location}：物種由「{$individual->species_name}」變成「{$species}」。";
            }
            if ($individual !== null && (int) $individual->survey_order !== $order) {
                $errors[] = "{$location}：調查順序由 {$individual->survey_order} 變成 {$row->survey_order}。";
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
        $now = now();

        foreach ($rows as $row) {
            $plotCode = trim((string) $row->plot);
            $number = trim((string) $row->individual_no);
            $key = $plotCode.'|'.$number;

            if (! isset($plots[$plotCode])) {
                $plotId = $db->table('plots')->insertGetId([
                    'experiment_id' => $experimentId,
                    'plot_code' => $plotCode,
                    'density' => null,
                    'note' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $plots[$plotCode] = (object) ['id' => $plotId];
                $createdPlots++;
            }

            if (! isset($individuals[$key])) {
                $individualId = $db->table('individuals')->insertGetId([
                    'plot_id' => $plots[$plotCode]->id,
                    'individual_no' => $number,
                    'survey_order' => (int) $row->survey_order,
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
                    'id' => $individualId,
                    'survey_order' => (int) $row->survey_order,
                    'species_name' => trim((string) $row->species),
                ];
                $createdIndividuals++;
            }

            $status = strtoupper(trim((string) $row->status));
            $db->table('records')->insert([
                'individual_id' => $individuals[$key]->id,
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

            if ($status === 'D') {
                $db->table('individuals')->where('id', $individuals[$key]->id)->update([
                    'death_date' => $date,
                    'updated_at' => $now,
                ]);
            } elseif ($status === 'G') {
                $db->table('individuals')->where('id', $individuals[$key]->id)->update([
                    'death_date' => $date,
                    'end_date' => $date,
                    'updated_at' => $now,
                ]);
            }
        }

        $db->table('experiments')->where('id', $experimentId)->update([
            'start_date' => $db->table(self::SOURCE)->min('survey_date'),
            'end_date' => null,
            'is_active' => true,
            'updated_at' => $now,
        ]);

        return compact('date', 'createdPlots', 'createdIndividuals', 'createdRecords');
    }

    private function plotMap(Connection $db, int $experimentId): array
    {
        return $db->table('plots')->where('experiment_id', $experimentId)
            ->get(['id', 'plot_code'])->keyBy('plot_code')->all();
    }

    private function individualMap(Connection $db, int $experimentId): array
    {
        return $db->table('individuals as individuals')
            ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
            ->where('plots.experiment_id', $experimentId)
            ->get(['individuals.id', 'individuals.individual_no', 'individuals.survey_order', 'individuals.species_name', 'plots.plot_code'])
            ->keyBy(fn (object $row): string => $row->plot_code.'|'.$row->individual_no)->all();
    }

    private function numericValue(mixed $value): ?float
    {
        return $value === null || $value === '' || ! is_numeric($value) ? null : (float) $value;
    }

    private function integerValue(mixed $value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value) || (float) $value !== floor((float) $value)) {
            return null;
        }

        return (int) $value;
    }
}
