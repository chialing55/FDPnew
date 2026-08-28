<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('mysql3');

        if (! $schema->hasColumn('seedling_individuals', 'spcode')) {
            $schema->table('seedling_individuals', function (Blueprint $table): void {
                $table->string('spcode', 50)->nullable()->after('csp')->index();
            });
        }

        $speciesByName = DB::connection('mysql4')
            ->table('spinfo')
            ->whereNotNull('csp')
            ->where('csp', '<>', '')
            ->pluck('spcode', 'csp');

        if (! $speciesByName->contains('UNKUNK')) {
            throw new RuntimeException('fs_base.spinfo 缺少 UNKUNK，無法回填未知小苗物種。');
        }

        DB::connection('mysql3')->transaction(function () use ($speciesByName): void {
            DB::connection('mysql3')->table('seedling_individuals')->update(['spcode' => 'UNKUNK']);

            foreach ($speciesByName as $csp => $spcode) {
                if (trim((string) $spcode) === '' || str_starts_with(strtoupper((string) $spcode), 'UNK')) {
                    continue;
                }

                DB::connection('mysql3')
                    ->table('seedling_individuals')
                    ->where('csp', $csp)
                    ->update(['spcode' => $spcode]);
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql3');

        if ($schema->hasColumn('seedling_individuals', 'spcode')) {
            $schema->table('seedling_individuals', function (Blueprint $table): void {
                $table->dropColumn('spcode');
            });
        }
    }
};
