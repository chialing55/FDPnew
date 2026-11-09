<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        Schema::connection($this->connection)->table('seedling_experiment_records', function (Blueprint $table): void {
            $table->char('survey_period', 7)->nullable()->after('survey_date')->index();
        });

        DB::connection($this->connection)->statement(
            "UPDATE seedling_experiment_records SET survey_period = DATE_FORMAT(survey_date, '%Y-%m')"
        );

        DB::connection($this->connection)->statement(
            'ALTER TABLE seedling_experiment_records MODIFY survey_period CHAR(7) NOT NULL'
        );

        Schema::connection($this->connection)->table('seedling_experiment_records', function (Blueprint $table): void {
            $table->unique(
                ['individual_id', 'survey_period'],
                'seedling_records_individual_period_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('seedling_experiment_records', function (Blueprint $table): void {
            $table->dropUnique('seedling_records_individual_period_unique');
            $table->dropIndex(['survey_period']);
            $table->dropColumn('survey_period');
        });
    }
};
