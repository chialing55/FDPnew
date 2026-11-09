<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    /**
     * @var array<string, string>
     */
    private array $tableNames = [
        'seedling_experiments' => 'experiments',
        'seedling_experiment_plots' => 'plots',
        'seedling_experiment_individuals' => 'individuals',
        'seedling_experiment_records' => 'records',
        'seedling_experiment_surveys' => 'surveys',
        'seedling_experiment_survey_individuals' => 'survey_individuals',
        'seedling_groundcover_phase1_raw' => 'groundcover_phase1_raw',
        'seedling_groundcover_phase1_cleaned' => 'groundcover_phase1_cleaned',
        'seedling_groundcover_phase2_raw' => 'groundcover_phase2_raw',
        'seedling_groundcover_phase2_cleaned' => 'groundcover_phase2_cleaned',
        'seedling_groundcover_phase2_raw_backup_20261030_before_reupload' => 'groundcover_phase2_raw_backup_20261030_before_reupload',
    ];

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        foreach ($this->tableNames as $oldName => $newName) {
            if ($schema->hasTable($oldName) && ! $schema->hasTable($newName)) {
                $schema->rename($oldName, $newName);
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        foreach (array_reverse($this->tableNames, true) as $oldName => $newName) {
            if ($schema->hasTable($newName) && ! $schema->hasTable($oldName)) {
                $schema->rename($newName, $oldName);
            }
        }
    }
};
