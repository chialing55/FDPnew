<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('mysql_web')->statement(
            'ALTER TABLE content_blocks MODIFY params JSON NULL'
        );
    }

    public function down(): void
    {
        DB::connection('mysql_web')->table('content_blocks')
            ->whereNull('params')
            ->update(['params' => json_encode([])]);

        DB::connection('mysql_web')->statement(
            'ALTER TABLE content_blocks MODIFY params JSON NOT NULL'
        );
    }
};
