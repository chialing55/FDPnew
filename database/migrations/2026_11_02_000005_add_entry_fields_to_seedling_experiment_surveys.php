<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        Schema::connection($this->connection)->table('seedling_experiment_surveys', function (Blueprint $table): void {
            $table->date('default_survey_date')->nullable()->after('survey_period');
            $table->unsignedBigInteger('started_by')->nullable()->after('prepared_by');
            $table->unsignedBigInteger('completed_by')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('seedling_experiment_surveys', function (Blueprint $table): void {
            $table->dropColumn(['default_survey_date', 'started_by', 'completed_by']);
        });
    }
};
