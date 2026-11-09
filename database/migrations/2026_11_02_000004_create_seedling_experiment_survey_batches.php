<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        Schema::connection($this->connection)->create('seedling_experiment_surveys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('experiment_id')->constrained('seedling_experiments');
            $table->char('survey_period', 7);
            $table->string('status')->default('prepared')->index();
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['experiment_id', 'survey_period'], 'seedling_surveys_experiment_period_unique');
        });

        Schema::connection($this->connection)->create('seedling_experiment_survey_individuals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('survey_id');
            $table->unsignedBigInteger('individual_id');
            $table->unsignedBigInteger('previous_record_id')->nullable();
            $table->unsignedInteger('survey_order');
            $table->unsignedBigInteger('record_id')->nullable();
            $table->timestamps();

            $table->foreign('survey_id', 'seed_survey_item_survey_fk')
                ->references('id')->on('seedling_experiment_surveys')->cascadeOnDelete();
            $table->foreign('individual_id', 'seed_survey_item_individual_fk')
                ->references('id')->on('seedling_experiment_individuals');
            $table->foreign('previous_record_id', 'seed_survey_item_previous_fk')
                ->references('id')->on('seedling_experiment_records')->nullOnDelete();
            $table->foreign('record_id', 'seed_survey_item_record_fk')
                ->references('id')->on('seedling_experiment_records')->nullOnDelete();
            $table->unique(['survey_id', 'individual_id'], 'seedling_survey_individual_unique');
            $table->unique(['survey_id', 'survey_order'], 'seedling_survey_order_unique');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('seedling_experiment_survey_individuals');
        Schema::connection($this->connection)->dropIfExists('seedling_experiment_surveys');
    }
};
