<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        $db = DB::connection($this->connection);
        $experimentId = $db->table('seedling_experiments')
            ->where('code', 'groundcover_phase1')
            ->value('id');

        if ($experimentId !== null) {
            $db->table('seedling_experiment_plots')
                ->where('experiment_id', $experimentId)
                ->update([
                    'density' => 1,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // The former NULL values cannot be distinguished from later curated
        // values, so this data normalization is intentionally irreversible.
    }
};
