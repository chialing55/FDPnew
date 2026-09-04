<?php

namespace App\Services\Fushan;

use App\Models\FsGeoTreeSurvey\Record1;
use App\Models\FsGeoTreeSurvey\Record2;

final class GeoTreeEntryStartingPointService
{
    public function __construct(private readonly GeoTreeEntryRowLockResolver $rowLocks)
    {
    }

    public function firstWithoutDate(string $entry): ?array
    {
        $model = $entry === '1' ? Record1::class : Record2::class;
        $records = $model::query()
            ->where('show', 1)
            ->where(function ($query): void {
                $query->whereNull('date')
                    ->orWhere('date', '')
                    ->orWhere('date', '0000-00-00');
            })
            ->orderByRaw('CAST(qx AS UNSIGNED)')
            ->orderByRaw('CAST(qy AS UNSIGNED)')
            ->orderBy('sqx')
            ->orderBy('sqy')
            ->orderBy('tag')
            ->orderBy('branch')
            ->get(['stemid', 'qx', 'qy', 'sqx', 'sqy'])
            ->map(fn ($row) => $row->toArray())
            ->all();
        $locked = array_fill_keys($this->rowLocks->resolve($records)['lockedStemids'], true);

        foreach ($records as $record) {
            if (!isset($locked[(string) $record['stemid']])) {
                return [
                    'qx' => (int) $record['qx'],
                    'qy' => (int) $record['qy'],
                    'sqx' => (int) $record['sqx'],
                    'sqy' => (int) $record['sqy'],
                    'stemid' => (string) $record['stemid'],
                ];
            }
        }

        return null;
    }
}
