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
        $schema = Schema::connection($this->connection);

        $schema->create('seedling_groundcover_phase1_raw', function (Blueprint $table): void {
            $table->id();
            $table->string('date')->nullable();
            $table->string('plot')->nullable()->index();
            $table->string('no')->nullable();
            $table->string('species')->nullable();
            $table->string('length')->nullable();
            $table->string('leaf_count')->nullable();
            $table->string('status')->nullable();
            $table->text('note')->nullable();
            $table->string('source_file');
            $table->string('source_sheet')->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->timestamps();

            $table->index(['source_file', 'source_sheet', 'source_row'], 'phase1_raw_source_index');
        });

        $schema->create('seedling_groundcover_phase1_cleaned', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('raw_id')->constrained('seedling_groundcover_phase1_raw');
            $table->date('survey_date')->nullable()->index();
            $table->string('plot')->nullable()->index();
            $table->string('individual_no')->nullable();
            $table->string('species')->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->integer('leaf_count')->nullable();
            $table->string('status')->nullable()->index();
            $table->text('note')->nullable();
            $table->text('clean_note')->nullable();
            $table->timestamps();

            $table->index(['plot', 'individual_no'], 'phase1_cleaned_individual_index');
        });

        $schema->create('seedling_groundcover_phase2_raw', function (Blueprint $table): void {
            $table->id();
            $table->string('date')->nullable();
            $table->string('plot')->nullable()->index();
            $table->string('density')->nullable();
            $table->string('no')->nullable();
            $table->string('species')->nullable();
            $table->string('length')->nullable();
            $table->string('cotyledon_count')->nullable();
            $table->string('leaf_count')->nullable();
            $table->string('status')->nullable();
            $table->text('note')->nullable();
            $table->string('source_file');
            $table->string('source_sheet')->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->timestamps();

            $table->index(['source_file', 'source_sheet', 'source_row'], 'phase2_raw_source_index');
        });

        $schema->create('seedling_groundcover_phase2_cleaned', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('raw_id')->constrained('seedling_groundcover_phase2_raw');
            $table->date('survey_date')->nullable()->index();
            $table->string('plot')->nullable()->index();
            $table->decimal('density', 10, 3)->nullable();
            $table->string('individual_no')->nullable();
            $table->string('species')->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->integer('cotyledon_count')->nullable();
            $table->integer('leaf_count')->nullable();
            $table->string('status')->nullable()->index();
            $table->text('note')->nullable();
            $table->text('clean_note')->nullable();
            $table->timestamps();

            $table->index(['plot', 'individual_no'], 'phase2_cleaned_individual_index');
        });

        $schema->create('seedling_experiments', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $schema->create('seedling_experiment_plots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('experiment_id')->constrained('seedling_experiments');
            $table->string('plot_code');
            $table->decimal('density', 10, 3)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['experiment_id', 'plot_code']);
        });

        $schema->create('seedling_experiment_individuals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plot_id')->constrained('seedling_experiment_plots');
            $table->string('individual_no');
            $table->unsignedBigInteger('species_id')->nullable()->index();
            $table->string('species_name')->nullable()->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('end_reason')->nullable()->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['plot_id', 'individual_no']);
        });

        $schema->create('seedling_experiment_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('individual_id')->constrained('seedling_experiment_individuals');
            $table->date('survey_date')->index();
            $table->string('status')->index();
            $table->decimal('length', 10, 2)->nullable();
            $table->integer('cotyledon_count')->nullable();
            $table->integer('leaf_count')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['individual_id', 'survey_date']);
        });

        $now = now();
        DB::connection($this->connection)->table('seedling_experiments')->insert([
            ['code' => 'groundcover_phase1', 'name' => '地被實驗第一期', 'description' => null, 'start_date' => null, 'end_date' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'groundcover_phase2', 'name' => '地被實驗第二期', 'description' => null, 'start_date' => null, 'end_date' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'greenhouse', 'name' => '溫室實驗', 'description' => null, 'start_date' => null, 'end_date' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        $schema->dropIfExists('seedling_experiment_records');
        $schema->dropIfExists('seedling_experiment_individuals');
        $schema->dropIfExists('seedling_experiment_plots');
        $schema->dropIfExists('seedling_experiments');
        $schema->dropIfExists('seedling_groundcover_phase2_cleaned');
        $schema->dropIfExists('seedling_groundcover_phase2_raw');
        $schema->dropIfExists('seedling_groundcover_phase1_cleaned');
        $schema->dropIfExists('seedling_groundcover_phase1_raw');
    }
};
