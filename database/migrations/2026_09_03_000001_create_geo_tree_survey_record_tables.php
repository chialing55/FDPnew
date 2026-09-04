<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_geo_tree_survey';

    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasTable('census5_part')) {
            throw new RuntimeException('建立 GEO-TREES 輸入表前，必須先建立 census5_part。');
        }

        if (!Schema::connection($this->connection)->hasTable('record1')) {
            DB::connection($this->connection)->statement('CREATE TABLE `record1` LIKE `census5_part`');
            DB::connection($this->connection)->statement('INSERT INTO `record1` SELECT * FROM `census5_part`');
            DB::connection($this->connection)->table('record1')->update([
                'dbh' => 0,
                'h2' => 0,
                'date' => '0000-00-00',
                'code' => '',
                'confirm' => '',
                'tofix' => '',
                'alternote' => '',
                'updated_at' => '',
                'updated_id' => '',
                'show' => 1,
            ]);
            DB::connection($this->connection)->table('record1')
                ->where('status', '-9')
                ->update(['status' => '']);
        }

        if (!Schema::connection($this->connection)->hasTable('record2')) {
            DB::connection($this->connection)->statement('CREATE TABLE `record2` LIKE `record1`');
            DB::connection($this->connection)->statement('INSERT INTO `record2` SELECT * FROM `record1`');
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('record2');
        Schema::connection($this->connection)->dropIfExists('record1');
    }
};
