<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('modules')->updateOrInsert(
            ['code' => 'mortality'],
            [
                'name' => '死亡率調查',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('modules')->where('code', 'mortality')->delete();
    }
};
