<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('mysql_web')->hasColumn('content_blocks', 'params')) {
            return;
        }

        DB::connection('mysql_web')->statement(
            'ALTER TABLE content_blocks MODIFY params JSON NULL'
        );
    }

    public function down(): void
    {
        if (! Schema::connection('mysql_web')->hasColumn('content_blocks', 'params')) {
            return;
        }

        DB::connection('mysql_web')->table('content_blocks')
            ->whereNull('params')
            ->update(['params' => json_encode([])]);

        DB::connection('mysql_web')->statement(
            'ALTER TABLE content_blocks MODIFY params JSON NOT NULL'
        );
    }
};
