<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $validChecklistCodes = DB::connection('plant_catalog')
            ->table('taiwan_checklist')
            ->pluck('spcode')
            ->mapWithKeys(fn ($spcode) => [trim((string) $spcode) => true]);

        $now = now();
        $species = DB::connection('njs_seedling')
            ->table('spinfo')
            ->whereNotNull('tai2_spcode')
            ->where('tai2_spcode', '<>', '')
            ->get(['csp', 'spcode', 'tai2_spcode'])
            ->map(function ($row) use ($validChecklistCodes, $now): ?array {
                $spcode = trim((string) $row->spcode);
                $checklistCode = trim((string) $row->tai2_spcode);

                if ($spcode === '' || str_starts_with(strtoupper($spcode), 'UNK')) {
                    return null;
                }

                if (! isset($validChecklistCodes[$checklistCode])) {
                    return null;
                }

                return [
                    'site' => 'nanjenshan',
                    'csp' => trim((string) $row->csp),
                    'spcode' => $spcode,
                    'code' => $checklistCode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($species !== []) {
            DB::connection('plant_catalog')
                ->table('site_species')
                ->upsert(
                    $species,
                    ['site', 'spcode'],
                    ['csp', 'code', 'updated_at']
                );
        }
    }

    public function down(): void
    {
        // Data-only migration: retain catalog rows that may later be curated.
    }
};
