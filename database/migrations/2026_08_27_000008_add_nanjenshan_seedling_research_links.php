<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $links = DB::connection('plant_catalog')
            ->table('site_species')
            ->where('site', 'nanjenshan')
            ->where('spcode', 'not like', 'UNK%')
            ->get(['spcode'])
            ->map(fn ($species) => [
                'site' => 'nanjenshan',
                'spcode' => $species->spcode,
                'research_code' => 'seedling',
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($links !== []) {
            DB::connection('plant_catalog')
                ->table('species_research_links')
                ->upsert(
                    $links,
                    ['site', 'spcode', 'research_code'],
                    ['updated_at']
                );
        }
    }

    public function down(): void
    {
        DB::connection('plant_catalog')
            ->table('species_research_links')
            ->where('site', 'nanjenshan')
            ->where('research_code', 'seedling')
            ->delete();
    }
};
