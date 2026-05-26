<?php

namespace App\Services;

use App\Models\FsSeedsDateinfo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;

class SeedsDateinfoSyncService
{
    public static function sync(): void
    {
        $rows = FsSeedsDateinfo::query()
            ->whereNotNull('date')
            ->where('date', '!=', '')
            ->orderBy('date')
            ->orderBy('census')
            ->get(['census', 'date']);

        $assignments = self::buildAssignments($rows);
        $yearColumn = self::yearColumn();

        foreach ($assignments as $assignment) {
            FsSeedsDateinfo::query()
                ->where('census', $assignment['census'])
                ->update([
                    $yearColumn => $assignment['year'],
                    'month' => $assignment['month'],
                    'date1' => $assignment['date1'],
                    'period' => $assignment['period'],
                ]);
        }
    }

    /**
     * @param  iterable<int, mixed>  $rows
     * @return array<int, array{census:int|string, year:int, month:string, date1:string, period:int}>
     */
    public static function buildAssignments(iterable $rows): array
    {
        $normalizedRows = collect($rows)
            ->map(function ($row) {
                $census = is_array($row) ? ($row['census'] ?? null) : ($row->census ?? null);
                $date = self::normalizeDate(is_array($row) ? ($row['date'] ?? null) : ($row->date ?? null));

                return $date ? ['census' => $census, 'date' => $date] : null;
            })
            ->filter()
            ->unique(fn (array $row) => $row['date']->toDateString())
            ->sortBy(fn (array $row) => $row['date']->toDateString())
            ->values();

        if ($normalizedRows->isEmpty()) {
            return [];
        }

        $baseCounts = [];
        foreach ($normalizedRows as $row) {
            $date = $row['date'];
            $baseDate = $date->day < 4 ? $date->subMonthNoOverflow() : $date;
            $yearMonth = $baseDate->format('Y-m');
            $baseCounts[$yearMonth] = ($baseCounts[$yearMonth] ?? 0) + 1;
        }

        $assignments = [];
        $previousActualYearMonth = null;

        foreach ($normalizedRows as $row) {
            $date = $row['date'];
            $actualYearMonth = $date->format('Y-m');
            $previousYearMonth = $date->subMonthNoOverflow()->format('Y-m');
            $isFirstOfMonth = $previousActualYearMonth !== null && $previousActualYearMonth !== $actualYearMonth;

            $modifyDate = match (true) {
                $isFirstOfMonth && ($baseCounts[$previousYearMonth] ?? 0) === 3 => $date->subMonthNoOverflow(),
                $date->day < 4 => $date->subMonthNoOverflow(),
                default => $date,
            };

            $month = $modifyDate->format('m');
            $year = $modifyDate->year;

            $assignments[] = [
                'census' => $row['census'],
                'year' => $year,
                'month' => $month,
                'date1' => sprintf('%04d-%02d-01', $year, (int) $month),
                'period' => (int) $month >= 9 ? $year - 2001 : $year - 2002,
            ];

            $previousActualYearMonth = $actualYearMonth;
        }

        return $assignments;
    }

    private static function normalizeDate(mixed $date): ?CarbonImmutable
    {
        if ($date instanceof CarbonImmutable) {
            return $date->startOfDay();
        }

        if ($date === null || $date === '' || $date === '0000-00-00') {
            return null;
        }

        return CarbonImmutable::parse($date)->startOfDay();
    }

    private static function yearColumn(): string
    {
        return Schema::connection('mysql2')->hasColumn('dateinfo', 'tyear') ? 'tyear' : 'year';
    }
}
