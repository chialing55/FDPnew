<?php

namespace App\Services\Fushan;

use App\Models\FsGeoTreeSurvey\Record1;
use App\Models\FsGeoTreeSurvey\Record2;
use App\Support\TreeEntry\TreeEntryComparator;

final class GeoTreeComparisonService
{
    public function __construct(
        private readonly GeoTreeEntryRowLockResolver $rowLocks,
        private readonly TreeEntryComparator $comparator,
    ) {
    }

    public function compare(): array
    {
        $columns = config('tree-entry.surveys.fushan_geo_trees.compareColumns', []);
        $selected = array_values(array_unique(array_merge(
            ['stemid', 'qx', 'qy', 'sqx', 'sqy', 'show'],
            array_keys($columns),
        )));
        $first = Record1::query()->where('show', 1)->get($selected)->map->toArray()->all();
        $second = Record2::query()->where('show', 1)->get($selected)->map->toArray()->all();
        $lockSource = collect(array_merge($first, $second))->unique('stemid')->values()->all();
        $locked = $this->rowLocks->resolve($lockSource)['lockedStemids'];

        $result = $this->comparator->compare($first, $second, $columns, $locked);
        $result['locked'] = count($locked);
        return $result;
    }
}
