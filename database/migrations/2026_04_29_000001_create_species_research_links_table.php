<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql4')->create('species_research_links', function (Blueprint $table) {
            $table->id();
            $table->string('spcode', 50);
            $table->string('research_code', 50);
            $table->timestamps();

            $table->unique(['spcode', 'research_code']);
            $table->index('research_code');
        });

        $now = now();
        $links = [];

        DB::connection('mysql4')
            ->table('spinfo')
            ->select('spcode', 'tree', 'seed', 'seedling')
            ->whereNotNull('spcode')
            ->orderBy('spcode')
            ->get()
            ->each(function ($species) use (&$links, $now) {
                foreach (['tree' => 'tree', 'seed' => 'seed', 'seedling' => 'seedling'] as $column => $researchCode) {
                    if ((int) ($species->{$column} ?? 0) !== 0) {
                        $links[$species->spcode.'|'.$researchCode] = [
                            'spcode' => $species->spcode,
                            'research_code' => $researchCode,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            });

        DB::connection('fs_mortality')
            ->table('tree_individuals')
            ->select('spcode')
            ->whereNotNull('spcode')
            ->where('spcode', '!=', '')
            ->distinct()
            ->orderBy('spcode')
            ->pluck('spcode')
            ->each(function ($spcode) use (&$links, $now) {
                $links[$spcode.'|mortality'] = [
                    'spcode' => $spcode,
                    'research_code' => 'mortality',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

        foreach (array_chunk(array_values($links), 500) as $chunk) {
            DB::connection('mysql4')->table('species_research_links')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::connection('mysql4')->dropIfExists('species_research_links');
    }
};
