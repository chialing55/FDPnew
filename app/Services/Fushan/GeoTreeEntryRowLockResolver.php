<?php

namespace App\Services\Fushan;

use App\Models\FsGeoTreeSurvey\Census5Part;
use App\Models\FsMortality\TreeIndividual;

final class GeoTreeEntryRowLockResolver
{
    public function resolve(array $records): array
    {
        $rules = config('tree-entry.surveys.fushan_geo_trees.rowLocks', []);
        $stemids = collect($records)->pluck('stemid')->filter()->map(fn ($value) => (string) $value)->unique()->values();
        $previousByStemid = Census5Part::query()
            ->whereIn('stemid', $stemids)
            ->get(['stemid', 'dbh', 'pom'])
            ->mapWithKeys(fn (Census5Part $row) => [(string) $row->stemid => $row->toArray()])
            ->all();
        $activeMortalityStemids = TreeIndividual::query()
            ->where('is_active', 1)
            ->whereIn('stemid', $stemids)
            ->pluck('stemid')
            ->mapWithKeys(fn ($stemid) => [(string) $stemid => true]);
        $lockedStemids = [];

        $displayRecords = collect($records)->map(function (array $record) use (
            $rules,
            $previousByStemid,
            $activeMortalityStemids,
            &$lockedStemids,
        ) {
            $stemid = (string) ($record['stemid'] ?? '');
            $previous = $previousByStemid[$stemid] ?? null;

            foreach ($rules as $rule) {
                $matches = match ($rule['type'] ?? '') {
                    'active_mortality' => $activeMortalityStemids->has($stemid),
                    'previous_dbh_below' => $previous !== null
                        && isset($rule['column'], $previous[$rule['column']])
                        && (float) $previous[$rule['column']] < (float) $rule['threshold'],
                    default => false,
                };

                if ($matches) {
                    $lockedStemids[$stemid] = true;
                    $record['_entryLock'] = [
                        'type' => $rule['type'],
                        'displayColumn' => $rule['displayColumn'],
                        'display' => $rule['display'],
                    ];
                    break;
                }
            }

            return $record;
        })->all();

        return [
            'records' => $displayRecords,
            'previousByStemid' => $previousByStemid,
            'lockedStemids' => array_keys($lockedStemids),
        ];
    }
}
