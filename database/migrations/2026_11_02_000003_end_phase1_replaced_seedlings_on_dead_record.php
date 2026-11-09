<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        DB::connection($this->connection)->statement(<<<'SQL'
            UPDATE seedling_experiment_individuals AS individuals
            INNER JOIN seedling_experiment_plots AS plots
                ON plots.id = individuals.plot_id
            INNER JOIN seedling_experiments AS experiments
                ON experiments.id = plots.experiment_id
            INNER JOIN seedling_experiment_records AS records
                ON records.individual_id = individuals.id
            SET individuals.end_date = records.survey_date,
                individuals.updated_at = CURRENT_TIMESTAMP
            WHERE experiments.code = 'groundcover_phase1'
              AND records.status = 'D'
              AND records.note LIKE '%補植%'
              AND individuals.end_date IS NULL
            SQL);
    }

    public function down(): void
    {
        // This migration records a confirmed historical interpretation. It is
        // intentionally not reversed because later curated end dates cannot
        // safely be distinguished from these backfilled values.
    }
};
