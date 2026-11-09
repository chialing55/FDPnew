<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        Schema::connection($this->connection)->table('seedling_experiment_records', function (Blueprint $table): void {
            $table->unique(['individual_id', 'survey_date'], 'seedling_records_individual_survey_unique');
        });

        Schema::connection($this->connection)->table('seedling_experiment_records', function (Blueprint $table): void {
            $table->dropIndex(['individual_id', 'survey_date']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('seedling_experiment_records', function (Blueprint $table): void {
            $table->index(['individual_id', 'survey_date']);
        });

        Schema::connection($this->connection)->table('seedling_experiment_records', function (Blueprint $table): void {
            $table->dropUnique('seedling_records_individual_survey_unique');
        });
    }
};
