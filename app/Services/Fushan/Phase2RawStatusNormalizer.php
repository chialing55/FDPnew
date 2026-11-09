<?php

namespace App\Services\Fushan;

use Illuminate\Database\Connection;

class Phase2RawStatusNormalizer
{
    private const TABLE = 'groundcover_phase2_raw';

    public function normalize(
        Connection $connection,
        string $table = self::TABLE,
        string $dateColumn = 'date',
        string $numberColumn = 'no'
    ): array {
        $stats = [
            'known_rows_deleted' => 0,
            'known_rows_inserted' => 0,
            'known_rows_corrected' => 0,
            'measured_status_to_alive' => 0,
            'minus_four_status_corrected' => 0,
            'leaf_count_to_minus_four' => 0,
            'cotyledon_count_to_minus_four' => 0,
            'single_d_changed_to_g' => 0,
            'premature_g_changed_to_d' => 0,
            'recovered_d_rows_backfilled' => 0,
            'rows_changed' => 0,
        ];
        $previousStatuses = [];

        $stats['known_rows_deleted'] = $connection->table($table)
            ->where('plot', 'GM003-R')
            ->where($numberColumn, 'E205')
            ->where($dateColumn, '2025-08-29')
            ->where('species', '黃杞')
            ->where('status', 'G')
            ->delete();

        $missingM819 = $connection->table($table)
            ->where('plot', 'PE001-DC')
            ->where($numberColumn, 'M819-1')
            ->where($dateColumn, '2025-08-29')
            ->doesntExist();

        if ($missingM819) {
            $reference = $connection->table($table)
                ->where('plot', 'PE001-DC')
                ->where($numberColumn, 'M819-1')
                ->orderBy($dateColumn)
                ->first();

            if ($reference !== null) {
                $stats['known_rows_inserted'] = $connection->table($table)->insert([
                    $dateColumn => '2025-08-29',
                    'survey_order' => $reference->survey_order,
                    'plot' => 'PE001-DC',
                    'density' => $reference->density,
                    $numberColumn => 'M819-1',
                    'species' => $reference->species,
                    'length' => '-1',
                    'cotyledon_count' => '-1',
                    'leaf_count' => '-1',
                    'status' => 'A',
                    'note' => null,
                    'source_file' => $reference->source_file,
                    'source_sheet' => $reference->source_sheet,
                    'source_row' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]) ? 1 : 0;
            }
        }

        $stats['known_rows_corrected'] += $connection->table($table)
            ->where($dateColumn, '2025-05-21')
            ->where('plot', 'GM002-DC')
            ->where($numberColumn, 'GM002-DR')
            ->where('species', '黃杞')
            ->where('length', '3')
            ->where('leaf_count', '6')
            ->update([
                'plot' => 'GM002-DR',
                $numberColumn => 'E352-3',
                'updated_at' => now(),
            ]);

        $stats['known_rows_corrected'] += $connection->table($table)
            ->where($dateColumn, '2025-07-30')
            ->where('plot', 'GM005-DC')
            ->where($numberColumn, 'E308')
            ->where('status', 'G')
            ->where('length', '-4')
            ->update([
                'status' => 'D',
                'updated_at' => now(),
            ]);

        // E352-3 belongs to GM002-DR. Five surveys were entered under the
        // adjacent GM002-DC plot, splitting one continuous individual record.
        $stats['known_rows_corrected'] += $connection->table($table)
            ->where('plot', 'GM002-DC')
            ->where($numberColumn, 'E352-3')
            ->update([
                'plot' => 'GM002-DR',
                'updated_at' => now(),
            ]);

        // M753-1 belongs to GM002-DC; only this survey was entered as DR.
        $stats['known_rows_corrected'] += $connection->table($table)
            ->where($dateColumn, '2025-11-26')
            ->where('plot', 'GM002-DR')
            ->where($numberColumn, 'M753-1')
            ->update([
                'plot' => 'GM002-DC',
                'updated_at' => now(),
            ]);

        // M761-1~3 form one continuous series in GM002-DR. The final three
        // surveys were entered as GM002-DC, but their historical records and
        // survey-order placement both belong to GM002-DR.
        $stats['known_rows_corrected'] += $connection->table($table)
            ->where('plot', 'GM002-DC')
            ->whereIn($numberColumn, ['M761-1', 'M761-2', 'M761-3'])
            ->whereIn($dateColumn, ['2026-07-20', '2026-08-19', '2026-09-20'])
            ->update([
                'plot' => 'GM002-DR',
                'updated_at' => now(),
            ]);

        $stats['known_rows_corrected'] += $connection->table($table)
            ->where($dateColumn, '2025-10-29')
            ->where('plot', 'GE002-DR')
            ->where($numberColumn, 'M804-2')
            ->update([
                'length' => '20.5',
                'updated_at' => now(),
            ]);

        // A single density cell was omitted in the final PE010-DR survey.
        // The same individual in all earlier surveys and every other seedling
        // in this plot use density 1.
        $stats['known_rows_corrected'] += $connection->table($table)
            ->where($dateColumn, '2026-09-20')
            ->where('plot', 'PE010-DR')
            ->where($numberColumn, 'E336')
            ->whereNull('density')
            ->update([
                'density' => 1,
                'updated_at' => now(),
            ]);

        $rows = $connection->table($table)
            ->select([
                'id',
                'plot',
                "{$numberColumn} as no",
                "{$dateColumn} as date",
                'length',
                'cotyledon_count',
                'leaf_count',
                'status',
            ])
            ->orderBy('plot')
            ->orderBy($numberColumn)
            ->orderBy($dateColumn)
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $key = trim((string) $row->plot).'|'.trim((string) $row->no);
            $status = $this->statusValue($row->status);
            $lengthIsMinusFour = $this->isMinusFour($row->length);
            $lengthIsMeasured = $this->isNumeric($row->length) && ! $lengthIsMinusFour;
            $newStatus = $status;
            $updates = [];

            if ($lengthIsMeasured && $status !== 'A') {
                $newStatus = 'A';
                $updates['status'] = 'A';
                $stats['measured_status_to_alive']++;
            } elseif ($lengthIsMinusFour && ($status === 'A' || $status === null)) {
                $previous = $previousStatuses[$key] ?? null;
                $newStatus = in_array($previous, ['D', 'G'], true) ? $previous : 'D';
                $updates['status'] = $newStatus;
                $stats['minus_four_status_corrected']++;
            }

            if ($lengthIsMinusFour && ! $this->isMinusFour($row->leaf_count)) {
                $updates['leaf_count'] = '-4';
                $stats['leaf_count_to_minus_four']++;
            }

            if ($lengthIsMinusFour && in_array($newStatus, ['D', 'G'], true) && ! $this->isMinusFour($row->cotyledon_count)) {
                $updates['cotyledon_count'] = '-4';
                $stats['cotyledon_count_to_minus_four']++;
            }

            if ($updates !== []) {
                $updates['updated_at'] = now();
                $connection->table($table)->where('id', $row->id)->update($updates);
                $stats['rows_changed']++;
            }

            $previousStatuses[$key] = $newStatus;
        }

        $recoveredRows = $connection->table($table)
            ->where('plot', 'GM002-DC')
            ->where($numberColumn, 'M742-3')
            ->whereIn($dateColumn, ['2025-07-30', '2025-08-29'])
            ->where(function ($query): void {
                $query->where('status', '!=', 'A')
                    ->orWhere('length', '!=', '-1')
                    ->orWhere('cotyledon_count', '!=', '-1')
                    ->orWhere('leaf_count', '!=', '-1');
            })
            ->update([
                'status' => 'A',
                'length' => '-1',
                'cotyledon_count' => '-1',
                'leaf_count' => '-1',
                'updated_at' => now(),
            ]);
        $stats['known_rows_corrected'] += $recoveredRows;

        $this->normalizeTerminalStatusSequences(
            $connection,
            $stats,
            $table,
            $dateColumn,
            $numberColumn
        );
        $this->backfillDeadRowsBeforeRecovery(
            $connection,
            $stats,
            $table,
            $dateColumn,
            $numberColumn
        );

        return $stats;
    }

    private function backfillDeadRowsBeforeRecovery(
        Connection $connection,
        array &$stats,
        string $table,
        string $dateColumn,
        string $numberColumn
    ): void {
        $groups = $connection->table($table)
            ->select(['id', 'plot', "{$numberColumn} as no", "{$dateColumn} as date", 'status'])
            ->orderBy('plot')
            ->orderBy($numberColumn)
            ->orderBy($dateColumn)
            ->orderBy('id')
            ->get()
            ->groupBy(fn (object $row): string => trim((string) $row->plot).'|'.trim((string) $row->no));

        foreach ($groups as $rows) {
            $rows = $rows->values();

            for ($index = 1; $index < $rows->count(); $index++) {
                if ($this->statusValue($rows[$index]->status) !== 'A'
                    || $this->statusValue($rows[$index - 1]->status) !== 'D') {
                    continue;
                }

                $deadIds = [];
                for ($cursor = $index - 1; $cursor >= 0; $cursor--) {
                    if ($this->statusValue($rows[$cursor]->status) !== 'D') {
                        break;
                    }
                    $deadIds[] = $rows[$cursor]->id;
                }

                if ($deadIds === []) {
                    continue;
                }

                $updated = $connection->table($table)->whereIn('id', $deadIds)->update([
                    'status' => 'A',
                    'length' => '-1',
                    'cotyledon_count' => '-1',
                    'leaf_count' => '-1',
                    'updated_at' => now(),
                ]);
                $stats['recovered_d_rows_backfilled'] += $updated;
            }
        }
    }

    private function normalizeTerminalStatusSequences(
        Connection $connection,
        array &$stats,
        string $table,
        string $dateColumn,
        string $numberColumn
    ): void {
        $groups = $connection->table($table)
            ->select(['id', 'plot', "{$numberColumn} as no", "{$dateColumn} as date", 'status'])
            ->orderBy('plot')
            ->orderBy($numberColumn)
            ->orderBy($dateColumn)
            ->orderBy('id')
            ->get()
            ->groupBy(fn (object $row): string => trim((string) $row->plot).'|'.trim((string) $row->no));

        foreach ($groups as $rows) {
            $rows = $rows->values();
            $firstGone = $rows->search(fn (object $row): bool => $this->statusValue($row->status) === 'G');

            if ($firstGone === false) {
                continue;
            }

            $tail = $rows->slice($firstGone)->values();
            $deadRows = $tail->filter(fn (object $row): bool => $this->statusValue($row->status) === 'D')->values();

            if ($deadRows->count() === 1) {
                $connection->table($table)->where('id', $deadRows->first()->id)->update([
                    'status' => 'G',
                    'updated_at' => now(),
                ]);
                $stats['single_d_changed_to_g']++;
                continue;
            }

            if ($deadRows->count() <= 1) {
                continue;
            }

            $firstDead = $tail->search(fn (object $row): bool => $this->statusValue($row->status) === 'D');
            $prematureGoneRows = $tail->slice(0, $firstDead)
                ->filter(fn (object $row): bool => $this->statusValue($row->status) === 'G');

            foreach ($prematureGoneRows as $row) {
                $connection->table($table)->where('id', $row->id)->update([
                    'status' => 'D',
                    'updated_at' => now(),
                ]);
                $stats['premature_g_changed_to_d']++;
            }
        }
    }

    private function statusValue(mixed $status): ?string
    {
        $value = strtoupper(trim((string) $status));

        return $value === '' ? null : $value;
    }

    private function isNumeric(mixed $value): bool
    {
        return trim((string) $value) !== '' && is_numeric($value);
    }

    private function isMinusFour(mixed $value): bool
    {
        return $this->isNumeric($value) && (float) $value === -4.0;
    }
}
