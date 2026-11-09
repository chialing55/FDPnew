<?php

namespace App\Services\Fushan;

use Illuminate\Database\Connection;

class Phase1RawStatusNormalizer
{
    private const TABLE = 'groundcover_phase1_raw';

    /**
     * Correct status values that contradict the recorded length.
     *
     * A numeric length other than -4 means the seedling was measured and is
     * alive. A -4 row cannot be alive; it keeps the preceding D/G state, or
     * starts as D when the preceding observation was alive or unavailable.
     */
    public function normalize(
        Connection $connection,
        string $table = self::TABLE,
        string $dateColumn = 'date',
        string $numberColumn = 'no'
    ): array
    {
        $stats = [
            'known_row_corrections' => 0,
            'duplicate_rows_deleted' => 0,
            'alive_with_minus_four' => 0,
            'blank_with_length' => 0,
            'non_alive_with_length' => 0,
            'blank_with_minus_four' => 0,
            'leaf_count_with_minus_four_length' => 0,
            'single_d_changed_to_g' => 0,
            'premature_g_changed_to_d' => 0,
            'total' => 0,
        ];
        $previousStatuses = [];

        $this->removeKnownDuplicates($connection, $stats, $table, $dateColumn, $numberColumn);
        $this->applyKnownCorrections($connection, $stats, $table, $dateColumn, $numberColumn);

        $rows = $connection->table($table)
            ->select(['id', 'plot', "{$numberColumn} as no", "{$dateColumn} as date", 'length', 'leaf_count', 'status'])
            ->orderBy('plot')
            ->orderBy($numberColumn)
            ->orderBy($dateColumn)
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $key = trim((string) $row->plot).'|'.trim((string) $row->no);
            $status = self::statusValue($row->status);
            $lengthType = self::lengthType($row->length);
            $newStatus = $status;
            $category = null;
            $updates = [];

            if ($lengthType === 'measured' && $status !== 'A') {
                $newStatus = 'A';
                $category = $status === null ? 'blank_with_length' : 'non_alive_with_length';
            } elseif ($lengthType === 'minus_four' && ($status === 'A' || $status === null)) {
                $previous = $previousStatuses[$key] ?? null;
                $newStatus = in_array($previous, ['D', 'G'], true) ? $previous : 'D';
                $category = $status === 'A' ? 'alive_with_minus_four' : 'blank_with_minus_four';
            }

            if ($category !== null && $newStatus !== $status) {
                $updates['status'] = $newStatus;
                $stats[$category]++;
            }

            if ($lengthType === 'minus_four' && self::lengthType($row->leaf_count) !== 'minus_four') {
                $updates['leaf_count'] = '-4';
                $stats['leaf_count_with_minus_four_length']++;
            }

            if ($updates !== []) {
                $updates['updated_at'] = now();
                $connection->table($table)->where('id', $row->id)->update($updates);
                $stats['total']++;
            }

            $previousStatuses[$key] = $newStatus;
        }

        $this->normalizeTerminalStatusSequences($connection, $stats, $table, $dateColumn, $numberColumn);

        return $stats;
    }

    private function normalizeTerminalStatusSequences(
        Connection $connection,
        array &$stats,
        string $table,
        string $dateColumn,
        string $numberColumn
    ): void
    {
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
            $firstG = $rows->search(fn (object $row): bool => self::statusValue($row->status) === 'G');

            if ($firstG === false) {
                continue;
            }

            $tail = $rows->slice($firstG)->values();
            $deadRows = $tail->filter(fn (object $row): bool => self::statusValue($row->status) === 'D')->values();

            if ($deadRows->count() === 1) {
                $connection->table($table)->where('id', $deadRows->first()->id)->update([
                    'status' => 'G',
                    'updated_at' => now(),
                ]);
                $stats['single_d_changed_to_g']++;
                $stats['total']++;

                continue;
            }

            if ($deadRows->count() <= 1) {
                continue;
            }

            $firstD = $tail->search(fn (object $row): bool => self::statusValue($row->status) === 'D');
            $prematureGoneRows = $tail->slice(0, $firstD)
                ->filter(fn (object $row): bool => self::statusValue($row->status) === 'G');

            foreach ($prematureGoneRows as $row) {
                $connection->table($table)->where('id', $row->id)->update([
                    'status' => 'D',
                    'updated_at' => now(),
                ]);
                $stats['premature_g_changed_to_d']++;
                $stats['total']++;
            }
        }
    }

    private function applyKnownCorrections(
        Connection $connection,
        array &$stats,
        string $table,
        string $dateColumn,
        string $numberColumn
    ): void
    {
        $corrections = [
            [
                'date' => '2025-06-25',
                'plot' => 'PE004-C',
                'no' => 'E297',
                'values' => ['length' => '-4', 'leaf_count' => '-4', 'status' => 'D'],
            ],
            [
                'date' => '2024-07-14',
                'plot' => 'GM005-C',
                'no' => 'E240',
                'values' => ['length' => '5', 'leaf_count' => '0', 'status' => 'A', 'note' => null],
            ],
            [
                'date' => null,
                'plot' => 'GM002-R',
                'no' => 'E359',
                'values' => ['no' => 'E259'],
            ],
            [
                'date' => '2024-06-13',
                'plot' => 'GM002-R',
                'no' => 'E183',
                'values' => ['note' => '補植。補植為E259，長度7，葉2。'],
            ],
            [
                'date' => '2024-05-17',
                'plot' => 'GM001-C',
                'no' => 'M175',
                'values' => ['note' => '補植為M272，長6，葉11。'],
            ],
            [
                'date' => '2024-02-16',
                'plot' => 'PM006-R',
                'no' => 'E135',
                'values' => ['note' => '補植為E254。'],
            ],
            [
                'date' => '2024-05-17',
                'plot' => 'GM003-C',
                'no' => 'E199',
                'values' => ['note' => '補植為E256。'],
            ],
            [
                'date' => '2024-05-17',
                'plot' => 'GM003-R',
                'no' => 'E208',
                'values' => ['note' => '補植為E257。'],
            ],
            [
                'date' => '2023-12-07',
                'plot' => 'PE010-C',
                'no' => 'M163',
                'values' => ['note' => '補植為M256。'],
            ],
        ];

        foreach ($corrections as $correction) {
            $values = $correction['values'];
            if (array_key_exists('no', $values) && $numberColumn !== 'no') {
                $values[$numberColumn] = $values['no'];
                unset($values['no']);
            }

            $query = $connection->table($table)
                ->where('plot', $correction['plot'])
                ->where($numberColumn, $correction['no']);

            if ($correction['date'] !== null) {
                $query->where($dateColumn, $correction['date']);
            }

            $rows = $query->get(['id', ...array_keys($values)]);

            foreach ($rows as $row) {
                $needsUpdate = collect($values)->contains(
                    fn (mixed $value, string $column) => $value === null
                        ? $row->{$column} !== null && trim((string) $row->{$column}) !== ''
                        : trim((string) $row->{$column}) !== $value
                );

                if (! $needsUpdate) {
                    continue;
                }

                $connection->table($table)->where('id', $row->id)->update([
                    ...$values,
                    'updated_at' => now(),
                ]);
                $stats['known_row_corrections']++;
                $stats['total']++;
            }
        }
    }

    private function removeKnownDuplicates(
        Connection $connection,
        array &$stats,
        string $table,
        string $dateColumn,
        string $numberColumn
    ): void
    {
        $ids = $connection->table($table)
            ->where($dateColumn, '2025-10-29')
            ->where('plot', 'PM006-R')
            ->where($numberColumn, 'M133')
            ->where('species', '香楠')
            ->where('length', '-4')
            ->where('leaf_count', '-4')
            ->where('status', 'G')
            ->whereNull('note')
            ->orderBy('id')
            ->pluck('id');

        if ($ids->count() <= 1) {
            return;
        }

        $deleted = $connection->table($table)->whereIn('id', $ids->slice(1))->delete();
        $stats['duplicate_rows_deleted'] += $deleted;
        $stats['total'] += $deleted;
    }

    private static function statusValue(mixed $status): ?string
    {
        $value = strtoupper(trim((string) $status));

        return $value === '' ? null : $value;
    }

    private static function lengthType(mixed $length): string
    {
        $value = trim((string) $length);

        if ($value === '' || ! is_numeric($value)) {
            return 'missing';
        }

        return (float) $value === -4.0 ? 'minus_four' : 'measured';
    }
}
