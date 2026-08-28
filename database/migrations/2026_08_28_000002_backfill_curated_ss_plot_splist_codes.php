<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $codes = [
            'RUELSI' => '527 012 03 0', // 紫花蘆莉草
            'FALLMU' => '317 005 19 1', // 臺灣何首烏
            'ARISHE' => '347 001 04 0', // 異葉馬兜鈴
            'IXORCH' => '516 019 05 0', // 仙丹花
            'ERIODE' => '407 008 01 1', // 恆春山枇杷
            'ACHYRU' => '326 001 01 2', // 臺灣牛膝
            'TRADSP' => '622 27 20 0',  // 蚌蘭
            'ZEUXFL' => '642 137 19 0', // 黃花線柱蘭
        ];

        foreach ($codes as $spcode => $checklistCode) {
            DB::connection('mysql5')
                ->table('splist')
                ->where('spcode', $spcode)
                ->where(function ($query): void {
                    $query->whereNull('code')->orWhere('code', '');
                })
                ->update(['code' => $checklistCode]);
        }
    }

    public function down(): void
    {
        // Data-only migration: do not erase values that may later be curated manually.
    }
};
