<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('njs_seedling');

        if (! $schema->hasColumn('seedling_individuals', 'spcode')) {
            $schema->table('seedling_individuals', function (Blueprint $table): void {
                $table->string('spcode', 50)
                    ->nullable()
                    ->after('standard_species_name')
                    ->index();
            });
        }

        $spcodeByCsp = DB::connection('njs_seedling')
            ->table('spinfo')
            ->whereNotNull('csp')
            ->where('csp', '<>', '')
            ->whereNotNull('spcode')
            ->where('spcode', '<>', '')
            ->pluck('spcode', 'csp');

        foreach ($spcodeByCsp as $csp => $spcode) {
            DB::connection('njs_seedling')
                ->table('seedling_individuals')
                ->where('standard_species_name', $csp)
                ->update(['spcode' => $spcode]);
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('njs_seedling');

        if ($schema->hasColumn('seedling_individuals', 'spcode')) {
            $schema->table('seedling_individuals', function (Blueprint $table): void {
                $table->dropColumn('spcode');
            });
        }
    }
};
