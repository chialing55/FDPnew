<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $checklistByChineseName = DB::connection('plant_catalog')
            ->table('taiwan_checklist')
            ->whereNotNull('chname')
            ->where('chname', '<>', '')
            ->get(['chname', 'spcode', 'spcode_current'])
            ->groupBy(fn ($row) => trim((string) $row->chname));

        $njsSpecies = DB::connection('njs_seedling')
            ->table('spinfo')
            ->where(function ($query): void {
                $query->whereNull('tai2_spcode')->orWhere('tai2_spcode', '');
            })
            ->get(['id', 'csp']);

        foreach ($njsSpecies as $species) {
            $currentSpcodes = $checklistByChineseName
                ->get(trim((string) $species->csp), collect())
                ->map(fn ($row) => trim((string) ($row->spcode_current ?: $row->spcode)))
                ->filter()
                ->unique()
                ->values();

            if ($currentSpcodes->count() !== 1) {
                continue;
            }

            DB::connection('njs_seedling')
                ->table('spinfo')
                ->where('id', $species->id)
                ->where(function ($query): void {
                    $query->whereNull('tai2_spcode')->orWhere('tai2_spcode', '');
                })
                ->update(['tai2_spcode' => $currentSpcodes->first()]);
        }

        $fushanWaterMelonCode = DB::connection('plant_catalog')
            ->table('site_species')
            ->where('site', 'fushan')
            ->where('csp', '水冬瓜')
            ->value('code');

        if ($fushanWaterMelonCode) {
            $currentCode = DB::connection('plant_catalog')
                ->table('taiwan_checklist')
                ->where('spcode', $fushanWaterMelonCode)
                ->value('spcode_current') ?: $fushanWaterMelonCode;

            DB::connection('njs_seedling')
                ->table('spinfo')
                ->where('csp', '水冬瓜')
                ->where(function ($query): void {
                    $query->whereNull('tai2_spcode')->orWhere('tai2_spcode', '');
                })
                ->update(['tai2_spcode' => $currentCode]);
        }
    }

    public function down(): void
    {
        // Data-only migration: do not erase values that may later be curated manually.
    }
};
