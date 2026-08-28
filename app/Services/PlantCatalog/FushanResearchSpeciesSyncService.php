<?php

namespace App\Services\PlantCatalog;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class FushanResearchSpeciesSyncService
{
    public function audit(): array
    {
        $hasSpcode = Schema::connection('mysql3')->hasColumn('seedling_individuals', 'spcode');
        $individuals = DB::connection('mysql3')->table('seedling_individuals');
        $total = (clone $individuals)->count();
        $known = $hasSpcode
            ? (clone $individuals)->whereNotNull('spcode')->where('spcode', '<>', '')->where('spcode', '<>', 'UNKUNK')->count()
            : 0;
        $unknown = $hasSpcode
            ? (clone $individuals)->where('spcode', 'UNKUNK')->count()
            : 0;

        $knownNames = DB::connection('plant_catalog')->table('site_species')->where('site', 'fushan')
            ->pluck('csp')->map(fn ($value) => trim((string) $value))->filter()->flip();
        $unmatchedNames = (clone $individuals)
            ->whereNotNull('csp')->where('csp', '<>', '')
            ->distinct()->pluck('csp')
            ->map(fn ($value) => trim((string) $value))
            ->reject(fn ($value) => $knownNames->has($value))
            ->sort()->values()->all();

        $catalogCandidates = $this->missingSiteSpeciesCandidates($this->sourceSpeciesCodes());

        return [
            'has_spcode' => $hasSpcode,
            'total' => $total,
            'known' => $known,
            'unknown' => $unknown,
            'blank' => $hasSpcode ? $total - $known - $unknown : $total,
            'unmatched_names' => $unmatchedNames,
            'missing_site_species' => $catalogCandidates['addable'],
            'unresolved_site_species' => $catalogCandidates['unresolved'],
        ];
    }

    public function sync(): array
    {
        if (! Schema::connection('mysql3')->hasColumn('seedling_individuals', 'spcode')) {
            throw new RuntimeException('seedling_individuals.spcode 尚未建立，請先執行 migration。');
        }

        if (! Schema::connection('plant_catalog')->hasTable('site_species')
            || ! Schema::connection('plant_catalog')->hasTable('species_research_links')) {
            throw new RuntimeException('plant_catalog 樣區名錄資料表尚未建立，請先執行 migration。');
        }

        $sources = $this->sourceSpeciesCodes();
        $catalogSync = $this->syncMissingSiteSpecies($sources);

        $known = DB::connection('plant_catalog')->table('site_species')->where('site', 'fushan')->pluck('spcode')
            ->map(fn ($value) => trim((string) $value))->filter()->unique()->flip();

        $valid = [];
        $ignored = [];
        foreach ($sources as $researchCode => $codes) {
            $normalized = collect($codes)->map(fn ($value) => trim((string) $value))->filter()->unique()->values();
            $valid[$researchCode] = $normalized
                ->filter(fn (string $code) => ! $this->isUnknown($code) && $known->has($code))
                ->sort()->values();
            $ignored[$researchCode] = $normalized
                ->reject(fn (string $code) => ! $this->isUnknown($code) && $known->has($code))
                ->sort()->values()->all();
        }

        $now = now();
        $rows = collect($valid)->flatMap(fn (Collection $codes, string $researchCode) => $codes->map(fn (string $spcode) => [
            'site' => 'fushan',
            'spcode' => $spcode,
            'research_code' => $researchCode,
            'created_at' => $now,
            'updated_at' => $now,
        ]))->values()->all();

        $db = DB::connection('plant_catalog');
        $before = $db->table('species_research_links')->where('site', 'fushan')->count();
        $db->transaction(function () use ($db, $rows): void {
            $db->table('species_research_links')->where('site', 'fushan')->delete();
            foreach (array_chunk($rows, 500) as $chunk) {
                $db->table('species_research_links')->insert($chunk);
            }
        });

        return [
            'before' => $before,
            'after' => count($rows),
            'counts' => collect($valid)->map->count()->all(),
            'ignored' => $ignored,
            'site_species_added' => $catalogSync['added'],
            'site_species_unresolved' => $catalogSync['unresolved'],
        ];
    }

    private function sourceSpeciesCodes(): array
    {
        return [
            'tree' => DB::connection('mysql1')->table('base')->distinct()->pluck('spcode'),
            'seed' => DB::connection('mysql2')->table('fulldata')->distinct()->pluck('sp'),
            'seedling' => DB::connection('mysql3')->table('seedling_individuals')->distinct()->pluck('spcode'),
            'mortality' => $this->mortalitySpeciesCodes(),
        ];
    }

    private function syncMissingSiteSpecies(array $sources): array
    {
        $candidates = $this->missingSiteSpeciesCandidates($sources);
        $now = now();
        $rows = collect($candidates['addable'])->map(fn (array $candidate) => [
            'site' => 'fushan',
            'csp' => $candidate['csp'],
            'spcode' => $candidate['spcode'],
            'code' => $candidate['code'],
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($rows !== []) {
            DB::connection('plant_catalog')->table('site_species')->upsert(
                $rows,
                ['site', 'spcode'],
                ['csp', 'code', 'updated_at']
            );
        }

        return [
            'added' => count($rows),
            'unresolved' => $candidates['unresolved'],
        ];
    }

    private function missingSiteSpeciesCandidates(array $sources): array
    {
        $researchesByCode = collect($sources)->flatMap(function ($codes, string $researchCode) {
            return collect($codes)->map(fn ($code) => [
                'spcode' => trim((string) $code),
                'research_code' => $researchCode,
            ]);
        })->filter(fn (array $row) => ! $this->isUnknown($row['spcode']))
            ->groupBy('spcode')
            ->map(fn (Collection $rows) => $rows->pluck('research_code')->unique()->sort()->values()->all());

        $existing = DB::connection('plant_catalog')->table('site_species')
            ->where('site', 'fushan')->pluck('spcode')
            ->map(fn ($value) => trim((string) $value))->flip();
        $missingCodes = $researchesByCode->keys()->reject(fn ($code) => $existing->has($code))->values();

        if ($missingCodes->isEmpty()) {
            return ['addable' => [], 'unresolved' => []];
        }

        $references = collect();
        DB::connection('mysql4')->table('spinfo')->whereIn('spcode', $missingCodes)
            ->get(['spcode', 'csp', 'code'])->each(function ($row) use ($references): void {
                $references->put(trim((string) $row->spcode), [
                    'csp' => trim((string) $row->csp),
                    'code' => trim((string) $row->code),
                    'source' => 'fs_base.spinfo',
                ]);
            });
        DB::connection('mysql2')->table('splist')->whereIn('sp', $missingCodes)
            ->get(['sp', 'csp'])->each(function ($row) use ($references): void {
                $references->put(trim((string) $row->sp), array_merge(
                    ['csp' => trim((string) $row->csp), 'code' => '', 'source' => 'fs_seeds.splist'],
                    $references->get(trim((string) $row->sp), [])
                ));
            });
        DB::connection('mysql3')->table('seedling_individuals')->whereIn('spcode', $missingCodes)
            ->whereNotNull('csp')->where('csp', '<>', '')->get(['spcode', 'csp'])
            ->each(function ($row) use ($references): void {
                $references->put(trim((string) $row->spcode), array_merge(
                    ['csp' => trim((string) $row->csp), 'code' => '', 'source' => 'fs_seedling.seedling_individuals'],
                    $references->get(trim((string) $row->spcode), [])
                ));
            });

        $checklist = DB::connection('plant_catalog')->table('taiwan_checklist')->get([
            'spcode', 'spcode_current', 'chname',
        ]);
        $checklistByCode = $checklist->keyBy(fn ($row) => trim((string) $row->spcode));
        $checklistByName = $checklist->filter(fn ($row) => trim((string) $row->chname) !== '')
            ->groupBy(fn ($row) => trim((string) $row->chname));

        $addable = [];
        $unresolved = [];
        foreach ($missingCodes as $spcode) {
            $reference = $references->get($spcode, ['csp' => '', 'code' => '', 'source' => '']);
            $csp = trim((string) ($reference['csp'] ?? ''));
            $code = trim((string) ($reference['code'] ?? ''));
            $checklistRow = $code !== '' ? $checklistByCode->get($code) : null;

            if ($checklistRow !== null) {
                $code = trim((string) ($checklistRow->spcode_current ?: $checklistRow->spcode));
            } elseif ($csp !== '') {
                $nameMatches = $checklistByName->get($csp, collect())
                    ->map(fn ($row) => trim((string) ($row->spcode_current ?: $row->spcode)))
                    ->filter()->unique()->values();
                $code = $nameMatches->count() === 1 ? (string) $nameMatches->first() : '';
            } else {
                $code = '';
            }

            $row = [
                'spcode' => $spcode,
                'csp' => $csp,
                'code' => $code,
                'researches' => $researchesByCode->get($spcode, []),
                'source' => $reference['source'] ?? '',
            ];

            if ($csp !== '' && $code !== '' && $checklistByCode->has($code)) {
                $addable[] = $row;
            } else {
                $row['reason'] = $csp === '' ? '找不到中文名' : '無法唯一連到 taiwan_checklist';
                $unresolved[] = $row;
            }
        }

        return ['addable' => $addable, 'unresolved' => $unresolved];
    }

    private function mortalitySpeciesCodes(): Collection
    {
        $tags = DB::connection('fs_mortality')->table('tree_individuals')
            ->whereNotNull('stemid')->where('stemid', '<>', '')->pluck('stemid')
            ->map(fn ($stemid) => substr(explode('.', trim((string) $stemid), 2)[0], 0, 6))
            ->filter()->unique()->values();

        return $tags->isEmpty()
            ? collect()
            : DB::connection('mysql1')->table('base')->whereIn('tag', $tags)->distinct()->pluck('spcode');
    }

    private function isUnknown(string $code): bool
    {
        $code = strtoupper(trim($code));

        return $code === '' || $code === 'NOTHING' || str_starts_with($code, 'UNK');
    }
}
