<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        DB::connection($this->connection)
            ->table('content_block_items')
            ->where('sort_order', 0)
            ->update(['sort_order' => 1]);

        DB::connection($this->connection)->statement(
            'ALTER TABLE content_block_items MODIFY sort_order INT UNSIGNED NOT NULL DEFAULT 1'
        );
    }

    public function down(): void
    {
        DB::connection($this->connection)->statement(
            'ALTER TABLE content_block_items MODIFY sort_order INT UNSIGNED NOT NULL DEFAULT 0'
        );
    }
};
