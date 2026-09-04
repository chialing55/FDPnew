<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_geo_tree_survey';

    public function up(): void
    {
        foreach (['record1', 'record2'] as $table) {
            if (Schema::connection($this->connection)->hasTable($table)) {
                DB::connection($this->connection)->statement("ALTER TABLE `{$table}` ENGINE = InnoDB");
            }
        }
    }

    public function down(): void
    {
        foreach (['record1', 'record2'] as $table) {
            if (Schema::connection($this->connection)->hasTable($table)) {
                DB::connection($this->connection)->statement("ALTER TABLE `{$table}` ENGINE = MyISAM");
            }
        }
    }
};
