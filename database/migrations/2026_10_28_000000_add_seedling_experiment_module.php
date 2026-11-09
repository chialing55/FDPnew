<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('modules')->updateOrInsert(
            ['code' => 'seedling-experiment'],
            [
                'name' => '小苗實驗',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('modules')->where('code', 'seedling-experiment')->delete();
    }
};
