<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('plant_catalog');

        if (! $schema->hasTable('site_species')) {
            $schema->create('site_species', function (Blueprint $table): void {
                $table->id();
                $table->string('site', 50);
                $table->string('csp')->nullable();
                $table->string('spcode', 50);
                $table->string('code', 50)->nullable();
                $table->timestamps();
                $table->unique(['site', 'spcode']);
                $table->index(['site', 'csp']);
                $table->index('code');
            });
        }

        if (! $schema->hasTable('species_research_links')) {
            $schema->create('species_research_links', function (Blueprint $table): void {
                $table->id();
                $table->string('site', 50);
                $table->string('spcode', 50);
                $table->string('research_code', 50);
                $table->timestamps();
                $table->unique(['site', 'spcode', 'research_code']);
                $table->index(['site', 'research_code']);
                $table->foreign(['site', 'spcode'])
                    ->references(['site', 'spcode'])
                    ->on('site_species')
                    ->cascadeOnDelete();
            });
        }

        $now = now();
        $species = DB::connection('mysql4')->table('spinfo')->get(['csp', 'spcode', 'code'])
            ->map(fn ($row) => [
                'site' => 'fushan', 'csp' => $row->csp, 'spcode' => $row->spcode, 'code' => $row->code,
                'created_at' => $now, 'updated_at' => $now,
            ])->all();
        $links = DB::connection('mysql4')->table('species_research_links')->get(['spcode', 'research_code'])
            ->map(fn ($row) => [
                'site' => 'fushan', 'spcode' => $row->spcode, 'research_code' => $row->research_code,
                'created_at' => $now, 'updated_at' => $now,
            ])->all();

        DB::connection('plant_catalog')->transaction(function () use ($species, $links): void {
            foreach (array_chunk($species, 500) as $chunk) {
                DB::connection('plant_catalog')->table('site_species')->insertOrIgnore($chunk);
            }
            foreach (array_chunk($links, 500) as $chunk) {
                DB::connection('plant_catalog')->table('species_research_links')->insertOrIgnore($chunk);
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('plant_catalog');
        $schema->dropIfExists('species_research_links');
        $schema->dropIfExists('site_species');
    }
};
