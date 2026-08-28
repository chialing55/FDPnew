<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $surveySpcodes = DB::connection('mysql5')
            ->table('1ha_base_2024')
            ->where(function ($query): void {
                $query->whereNull('deleted_at')->orWhere('deleted_at', '');
            })
            ->whereNotNull('spcode')
            ->where('spcode', '<>', '')
            ->where('spcode', 'not like', 'UNK%')
            ->distinct()
            ->pluck('spcode');

        $validChecklistCodes = DB::connection('plant_catalog')
            ->table('taiwan_checklist')
            ->pluck('spcode')
            ->mapWithKeys(fn ($code) => [trim((string) $code) => true]);

        $now = now();
        $species = DB::connection('mysql5')
            ->table('splist')
            ->whereIn('spcode', $surveySpcodes)
            ->get(['spcode', 'index', 'code'])
            ->map(function ($row) use ($validChecklistCodes, $now): ?array {
                $spcode = trim((string) $row->spcode);
                $code = trim((string) $row->code);

                if ($spcode === '' || $code === '' || ! isset($validChecklistCodes[$code])) {
                    return null;
                }

                return [
                    'site' => 'shoushan',
                    'csp' => trim((string) $row->index),
                    'spcode' => $spcode,
                    'code' => $code,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter()
            ->values()
            ->all();

        DB::connection('plant_catalog')->transaction(function () use ($species, $now): void {
            if ($species === []) {
                return;
            }

            DB::connection('plant_catalog')
                ->table('site_species')
                ->upsert(
                    $species,
                    ['site', 'spcode'],
                    ['csp', 'code', 'updated_at']
                );

            $links = array_map(fn (array $row) => [
                'site' => $row['site'],
                'spcode' => $row['spcode'],
                'research_code' => 'tree',
                'created_at' => $now,
                'updated_at' => $now,
            ], $species);

            DB::connection('plant_catalog')
                ->table('species_research_links')
                ->upsert(
                    $links,
                    ['site', 'spcode', 'research_code'],
                    ['updated_at']
                );
        });
    }

    public function down(): void
    {
        // Data-only migration: retain catalog rows that may later be curated.
    }
};
