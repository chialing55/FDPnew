<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->create('censuses', function (Blueprint $table) {
            $table->id();
            $table->integer('census')->unique();
            $table->string('survey_year', 50);
            $table->boolean('has_dbh');
            $table->integer('data_batch');
            $table->timestamps();
        });

        DB::connection('fs_mortality')->table('censuses')->insert([
            [
                'id' => 1,
                'census' => 1,
                'survey_year' => '2017',
                'has_dbh' => 1,
                'data_batch' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'census' => 2,
                'survey_year' => '2018',
                'has_dbh' => 0,
                'data_batch' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'census' => 3,
                'survey_year' => '2019',
                'has_dbh' => 0,
                'data_batch' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'census' => 4,
                'survey_year' => '2020',
                'has_dbh' => 1,
                'data_batch' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'census' => 5,
                'survey_year' => '2021',
                'has_dbh' => 1,
                'data_batch' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'census' => 6,
                'survey_year' => '2022',
                'has_dbh' => 1,
                'data_batch' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'census' => 7,
                'survey_year' => '2023',
                'has_dbh' => 0,
                'data_batch' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'census' => 8,
                'survey_year' => '2024',
                'has_dbh' => 0,
                'data_batch' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'census' => 9,
                'survey_year' => '2025',
                'has_dbh' => 1,
                'data_batch' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'census' => 10,
                'survey_year' => '2026',
                'has_dbh' => 1,
                'data_batch' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->dropIfExists('censuses');
    }
};
