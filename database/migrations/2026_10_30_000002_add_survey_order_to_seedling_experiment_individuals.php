<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        Schema::connection($this->connection)->table('seedling_experiment_individuals', function (Blueprint $table): void {
            $table->unsignedInteger('survey_order')->nullable()->after('individual_no')->index();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('seedling_experiment_individuals', function (Blueprint $table): void {
            $table->dropColumn('survey_order');
        });
    }
};
