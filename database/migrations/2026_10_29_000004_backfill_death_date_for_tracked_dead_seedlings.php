<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        $db = DB::connection($this->connection);

        $db->table('seedling_experiment_individuals')
            ->whereNull('end_date')
            ->orderBy('id')
            ->each(function (object $individual) use ($db): void {
                $latestStatus = $db->table('seedling_experiment_records')
                    ->where('individual_id', $individual->id)
                    ->orderByDesc('survey_date')
                    ->value('status');

                if ($latestStatus !== 'D') {
                    return;
                }

                $lastAliveDate = $db->table('seedling_experiment_records')
                    ->where('individual_id', $individual->id)
                    ->where('status', 'A')
                    ->max('survey_date');
                $firstDeadDate = $db->table('seedling_experiment_records')
                    ->where('individual_id', $individual->id)
                    ->where('status', 'D')
                    ->when($lastAliveDate, fn ($query) => $query->where('survey_date', '>', $lastAliveDate))
                    ->min('survey_date');

                if ($firstDeadDate !== null) {
                    $db->table('seedling_experiment_individuals')->where('id', $individual->id)->update([
                        'death_date' => $firstDeadDate,
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::connection($this->connection)
            ->table('seedling_experiment_individuals')
            ->whereNull('end_date')
            ->update(['death_date' => null]);
    }
};
