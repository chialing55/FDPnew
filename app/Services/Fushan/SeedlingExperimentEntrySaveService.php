<?php

namespace App\Services\Fushan;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class SeedlingExperimentEntrySaveService
{
    private const CONNECTION = 'fs_seedling_exp';

    private const IDENTITY_FIELDS = ['plot', 'density', 'individual_no', 'species'];

    /**
     * Revalidate every batch item from persisted records before completing a survey.
     *
     * @return array<int, string>
     */
    public function completionErrors(int $surveyId): array
    {
        $connection = DB::connection(self::CONNECTION);
        $survey = $connection->table('surveys')->find($surveyId);
        if ($survey === null) {
            return ['找不到目前調查批次。'];
        }

        $items = $connection->table('survey_individuals as batch_items')
            ->join('individuals as individuals', 'individuals.id', '=', 'batch_items.individual_id')
            ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
            ->leftJoin('records as records', 'records.id', '=', 'batch_items.record_id')
            ->leftJoin('records as previous', 'previous.id', '=', 'batch_items.previous_record_id')
            ->where('batch_items.survey_id', $surveyId)
            ->orderBy('batch_items.survey_order')
            ->get([
                'batch_items.id as batch_item_id',
                'batch_items.individual_id',
                'batch_items.record_id',
                'plots.plot_code as plot',
                'plots.density',
                'individuals.individual_no',
                'individuals.species_name as species',
                'records.individual_id as record_individual_id',
                'records.survey_date as date',
                'records.survey_period',
                'records.length',
                'records.cotyledon_count',
                'records.leaf_count',
                'records.status',
                'records.note',
                'previous.cotyledon_count as previous_cotyledon_count',
            ]);

        $errors = [];
        foreach ($items as $index => $item) {
            $label = "{$item->plot} / {$item->individual_no}";
            if ($item->record_id === null) {
                $errors[] = "{$label}：尚未輸入。";

                continue;
            }
            if ((int) $item->record_individual_id !== (int) $item->individual_id) {
                $errors[] = "{$label}：調查紀錄連結到錯誤的個體。";

                continue;
            }
            if ((string) $item->survey_period !== (string) $survey->survey_period) {
                $errors[] = "{$label}：調查年月不是 {$survey->survey_period}。";

                continue;
            }

            [, $rowErrors] = $this->validateRow([
                'batch_item_id' => $item->batch_item_id,
                'date' => $item->date,
                'length' => $item->length,
                'cotyledon_count' => $item->cotyledon_count,
                'leaf_count' => $item->leaf_count,
                'status' => $item->status,
                'note' => $item->note,
            ], $item, $index, $survey->survey_period);

            foreach ($rowErrors as $rowError) {
                $errors[] = "{$label}：{$rowError['message']}";
            }
        }

        return $errors;
    }

    /**
     * @return array{ok:bool, errors:array<int, array<string, mixed>>, changed?:int, skipped?:int}
     */
    public function saveGroup(int $surveyId, string $plotGroup, array $submittedRows): array
    {
        $connection = DB::connection(self::CONNECTION);
        $survey = $connection->table('surveys')->find($surveyId);

        if ($survey === null || ! in_array($survey->status, ['prepared', 'in_progress'], true)) {
            return $this->failure('目前調查批次不存在或已完成。');
        }

        $storedByItem = $this->storedRows($surveyId, $plotGroup)->keyBy('batch_item_id');
        $errors = [];
        $changed = 0;
        $skipped = 0;
        $seen = [];

        foreach (array_values($submittedRows) as $index => $row) {
            if ($this->isBlankLength($row['length'] ?? null)) {
                $skipped++;

                continue;
            }

            $itemId = filter_var($row['batch_item_id'] ?? null, FILTER_VALIDATE_INT);
            if ($itemId === false || ! $storedByItem->has($itemId)) {
                $errors[] = $this->error($index, 'individual_no', 'scope', '資料不屬於目前的 Plot，請重新載入。');

                continue;
            }
            if (isset($seen[$itemId])) {
                $errors[] = $this->error($index, 'individual_no', 'duplicate_row', '同一株小苗重複出現在送出資料中。');

                continue;
            }
            $seen[$itemId] = true;
            $stored = $storedByItem->get($itemId);
            $identityErrors = $this->validateIdentity($row, $stored, $index);
            [$normalized, $rowErrors] = $this->validateRow($row, $stored, $index, $survey->survey_period);
            $currentErrors = [...$identityErrors, ...$rowErrors];

            if ($currentErrors !== []) {
                array_push($errors, ...$currentErrors);

                continue;
            }

            try {
                $rowChanged = $connection->transaction(function () use ($connection, $survey, $normalized, $stored): int {
                    $row = $normalized;
                    $rowChanged = 0;
                    $this->backfillRecoveredRecords((int) $stored->individual_id, $row['status']);

                    $values = [
                        'individual_id' => $stored->individual_id,
                        'survey_date' => $row['date'],
                        'survey_period' => $survey->survey_period,
                        'status' => $row['status'],
                        'length' => $row['length'],
                        'cotyledon_count' => $row['cotyledon_count'],
                        'leaf_count' => $row['leaf_count'],
                        'note' => $row['note'],
                        'is_backfilled' => false,
                        'updated_at' => now(),
                    ];

                    if ($stored->record_id !== null) {
                        $record = $connection->table('records')
                            ->where('id', $stored->record_id)
                            ->where('individual_id', $stored->individual_id)
                            ->first();

                        if ($record !== null && $this->recordChanged($record, $values)) {
                            $connection->table('records')
                                ->where('id', $record->id)
                                ->update($values);
                            $rowChanged++;
                        }
                        $recordId = $record?->id;
                    } else {
                        $existing = $connection->table('records')
                            ->where('individual_id', $stored->individual_id)
                            ->where('survey_period', $survey->survey_period)
                            ->first();

                        if ($existing !== null) {
                            $recordId = $existing->id;
                            if ($this->recordChanged($existing, $values)) {
                                $connection->table('records')
                                    ->where('id', $existing->id)
                                    ->update($values);
                                $rowChanged++;
                            }
                        } else {
                            $recordId = $connection->table('records')->insertGetId([
                                ...$values,
                                'created_at' => now(),
                            ]);
                            $rowChanged++;
                        }

                        $connection->table('survey_individuals')
                            ->where('id', $stored->batch_item_id)
                            ->update([
                                'record_id' => $recordId,
                                'updated_at' => now(),
                            ]);
                    }

                    $this->refreshIndividualTracking((int) $stored->individual_id);

                    return $rowChanged;
                });
                $changed += $rowChanged;
            } catch (\Throwable $exception) {
                report($exception);
                $errors[] = $this->error($index, 'length', 'save_failed', '這筆資料儲存失敗，內容已保留，請修正或稍後再試。');
            }
        }

        $failed = collect($errors)->pluck('row')->unique()->count();

        return [
            'ok' => $errors === [],
            'status' => \App\Support\DataEntry\SaveFeedback::status($changed, $failed),
            'errors' => $errors,
            'changed' => $changed,
            'skipped' => $skipped,
            'failed' => $failed,
            'counts' => ['saved' => $changed, 'skipped' => $skipped, 'failed' => $failed],
        ];
    }

    private function storedRows(int $surveyId, string $plotGroup)
    {
        return DB::connection(self::CONNECTION)
            ->table('survey_individuals as batch_items')
            ->join('individuals as individuals', 'individuals.id', '=', 'batch_items.individual_id')
            ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
            ->leftJoin('records as records', 'records.id', '=', 'batch_items.record_id')
            ->leftJoin('records as previous', 'previous.id', '=', 'batch_items.previous_record_id')
            ->where('batch_items.survey_id', $surveyId)
            ->where('plots.plot_code', 'like', $plotGroup.'-%')
            ->orderBy('batch_items.survey_order')
            ->select([
                'batch_items.id as batch_item_id',
                'batch_items.individual_id',
                'batch_items.record_id',
                'plots.plot_code as plot',
                'plots.density',
                'individuals.individual_no',
                'individuals.species_name as species',
                'previous.cotyledon_count as previous_cotyledon_count',
            ])
            ->get();
    }

    private function validateIdentity(array $row, object $stored, int $index): array
    {
        $errors = [];

        foreach (self::IDENTITY_FIELDS as $field) {
            $submitted = $row[$field] ?? '';
            $expectedValue = $stored->{$field};
            $same = $field === 'density'
                ? (float) $submitted === (float) $expectedValue
                : (string) $submitted === (string) $expectedValue;
            if (! $same) {
                $errors[] = $this->error($index, $field, 'identity_changed', "{$field} 為唯讀欄位，不可修改。");
            }
        }

        return $errors;
    }

    private function isBlankLength(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    /**
     * @return array{0:array<string, mixed>, 1:array<int, array<string, mixed>>}
     */
    private function validateRow(array $row, object $stored, int $index, string $surveyPeriod): array
    {
        $errors = [];
        $date = trim((string) ($row['date'] ?? ''));
        $status = strtoupper(trim((string) ($row['status'] ?? '')));
        $note = trim((string) ($row['note'] ?? ''));

        try {
            $parsedDate = CarbonImmutable::createFromFormat('!Y-m-d', $date);
            if ($parsedDate->format('Y-m-d') !== $date) {
                throw new \RuntimeException;
            }
        } catch (\Throwable) {
            $parsedDate = null;
            $errors[] = $this->error($index, 'date', 'invalid_date', 'Date 必須是有效的 YYYY-MM-DD 日期。');
        }

        if ($parsedDate !== null && $parsedDate->format('Y-m') !== $surveyPeriod) {
            $errors[] = $this->error($index, 'date', 'wrong_period', "調查日期必須屬於 {$surveyPeriod}。");
        }

        if (! in_array($status, ['A', 'D', 'G'], true)) {
            $errors[] = $this->error($index, 'status', 'invalid_status', 'Status 必須為 A、D 或 G。');
        }

        $measurements = [];
        foreach (['length', 'cotyledon_count', 'leaf_count'] as $field) {
            $value = $row[$field] ?? null;
            if ($value === '' || $value === null || ! is_numeric($value)) {
                $errors[] = $this->error($index, $field, 'required_numeric', "{$field} 必須輸入數值。");
                $measurements[$field] = null;

                continue;
            }
            if ($field !== 'length' && (float) $value !== floor((float) $value)) {
                $errors[] = $this->error($index, $field, 'integer_required', "{$field} 必須是整數。");
            }
            $measurements[$field] = $field === 'length' ? (float) $value : (int) $value;
        }

        if (in_array($status, ['D', 'G'], true)) {
            foreach ($measurements as $field => $value) {
                if ($value !== null && (float) $value !== -4.0) {
                    $errors[] = $this->error($index, $field, 'dead_measurement', 'Status 為 D／G 時，量測欄位必須為 -4。');
                }
            }
        } elseif ($status === 'A') {
            foreach ($measurements as $field => $value) {
                if ($value !== null && (float) $value < 0) {
                    $errors[] = $this->error($index, $field, 'alive_measurement', 'Status 為 A 時，請輸入本次實際量測值。');
                }
            }

            $cotyledon = $measurements['cotyledon_count'];
            if ($stored->species === '香楠' && $cotyledon !== null && $cotyledon !== 0) {
                $errors[] = $this->error($index, 'cotyledon_count', 'xiangnan_cotyledon', '香楠不可有子葉數，請輸入 0。');
            }
            if ($stored->species === '黃杞' && $cotyledon !== null) {
                if ($cotyledon > 2) {
                    $errors[] = $this->error($index, 'cotyledon_count', 'huangqi_cotyledon_max', '黃杞子葉數最多為 2。');
                }
                $previousCotyledon = $stored->previous_cotyledon_count;
                if ($previousCotyledon !== null && (int) $previousCotyledon >= 0 && $cotyledon > (int) $previousCotyledon) {
                    $errors[] = $this->error($index, 'cotyledon_count', 'huangqi_cotyledon_increase', "黃杞子葉數不可比上次增加（上次為 {$previousCotyledon}）。");
                }
            }
        }

        return [[
            'batch_item_id' => (int) ($row['batch_item_id'] ?? 0),
            'date' => $date,
            'status' => $status,
            'length' => $measurements['length'],
            'cotyledon_count' => $measurements['cotyledon_count'],
            'leaf_count' => $measurements['leaf_count'],
            'note' => $note === '' ? null : $note,
        ], $errors];
    }

    private function backfillRecoveredRecords(int $individualId, string $newStatus): void
    {
        if ($newStatus !== 'A') {
            return;
        }

        $connection = DB::connection(self::CONNECTION);
        $trailingDead = $connection->table('records')
            ->where('individual_id', $individualId)
            ->orderByDesc('survey_date')
            ->orderByDesc('id')
            ->get()
            ->takeWhile(fn (object $record): bool => in_array($record->status, ['D', 'G'], true));

        if ($trailingDead->isEmpty()) {
            return;
        }

        $connection->table('records')
            ->whereIn('id', $trailingDead->pluck('id'))
            ->update([
                'status' => 'A',
                'length' => -1,
                'cotyledon_count' => -1,
                'leaf_count' => -1,
                'is_backfilled' => true,
                'updated_at' => now(),
            ]);
    }

    private function refreshIndividualTracking(int $individualId): void
    {
        $connection = DB::connection(self::CONNECTION);
        $records = $connection->table('records')
            ->where('individual_id', $individualId)
            ->orderBy('survey_date')
            ->orderBy('id')
            ->get(['survey_date', 'status']);

        $latest = $records->last();
        $deathDate = null;
        $endDate = null;

        if ($latest !== null && in_array($latest->status, ['D', 'G'], true)) {
            $trailing = $records->reverse()
                ->takeWhile(fn (object $record): bool => in_array($record->status, ['D', 'G'], true))
                ->reverse()
                ->values();
            $deathDate = $trailing->first()?->survey_date;
            $endDate = $latest->status === 'G' ? $latest->survey_date : null;
        }

        $connection->table('individuals')
            ->where('id', $individualId)
            ->update([
                'death_date' => $deathDate,
                'end_date' => $endDate,
                'updated_at' => now(),
            ]);
    }

    private function recordChanged(object $record, array $values): bool
    {
        foreach (['survey_date', 'survey_period', 'status'] as $field) {
            if ((string) ($record->{$field} ?? '') !== (string) ($values[$field] ?? '')) {
                return true;
            }
        }

        foreach (['length', 'cotyledon_count', 'leaf_count'] as $field) {
            $stored = $record->{$field} ?? null;
            $submitted = $values[$field] ?? null;
            if ($stored === null || $submitted === null) {
                if ($stored !== $submitted) {
                    return true;
                }

                continue;
            }

            if ((float) $stored !== (float) $submitted) {
                return true;
            }
        }

        $storedNote = trim((string) ($record->note ?? ''));
        $submittedNote = trim((string) ($values['note'] ?? ''));
        if ($storedNote !== $submittedNote) {
            return true;
        }

        if ((bool) ($record->is_backfilled ?? false) !== (bool) ($values['is_backfilled'] ?? false)) {
            return true;
        }

        return false;
    }

    private function failure(string $message): array
    {
        return [
            'ok' => false,
            'status' => 'error',
            'errors' => [$this->error(0, 'date', 'request', $message)],
            'changed' => 0,
            'skipped' => 0,
            'failed' => 1,
            'counts' => ['saved' => 0, 'skipped' => 0, 'failed' => 1],
        ];
    }

    private function error(int $row, string $field, string $code, string $message): array
    {
        return compact('row', 'field', 'code', 'message');
    }
}
