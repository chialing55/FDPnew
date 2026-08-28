<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('mysql2');

        if (! $schema->hasColumn('splist', 'fruiting_ht')) {
            $schema->table('splist', function (Blueprint $table): void {
                $table->char('fruiting_ht', 1)->nullable()->after('size');
            });
        }

        $fruitingHeights = DB::connection('mysql4')
            ->table('seeds_spinfo')
            ->whereNotNull('sp')
            ->where('sp', '<>', '')
            ->pluck('fruiting_ht', 'sp');

        DB::connection('mysql2')->transaction(function () use ($fruitingHeights): void {
            DB::connection('mysql2')->table('splist')->update(['fruiting_ht' => null]);

            foreach ($fruitingHeights as $spcode => $fruitingHeight) {
                DB::connection('mysql2')
                    ->table('splist')
                    ->where('sp', $spcode)
                    ->update([
                        'fruiting_ht' => trim((string) $fruitingHeight) ?: null,
                    ]);
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql2');

        if ($schema->hasColumn('splist', 'fruiting_ht')) {
            $schema->table('splist', function (Blueprint $table): void {
                $table->dropColumn('fruiting_ht');
            });
        }
    }
};
