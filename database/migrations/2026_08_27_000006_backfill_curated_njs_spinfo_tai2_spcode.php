<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $spcodes = [
            '大冇榕' => '308 006 30 0',
            '島榕' => '308 006 38 0',
            '水同木' => '308 006 11 0',
            '石苓舅' => '417 008 02 0',
            '臺灣梭羅樹' => '442 009 01 0',
            '錦蘭' => '514 005 02 0',
        ];

        foreach ($spcodes as $csp => $tai2Spcode) {
            DB::connection('njs_seedling')
                ->table('spinfo')
                ->where('csp', $csp)
                ->where(function ($query): void {
                    $query->whereNull('tai2_spcode')->orWhere('tai2_spcode', '');
                })
                ->update(['tai2_spcode' => $tai2Spcode]);
        }
    }

    public function down(): void
    {
        // Data-only migration: do not erase values that may later be curated manually.
    }
};
