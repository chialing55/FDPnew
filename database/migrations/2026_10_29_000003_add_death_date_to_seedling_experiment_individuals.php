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
        Schema::connection($this->connection)->table('seedling_experiment_individuals', function (Blueprint $table): void {
            $table->date('death_date')->nullable()->after('end_date')->index();
        });

        $db = DB::connection($this->connection);
        $db->table('seedling_experiment_individuals')
            ->whereNotNull('end_date')
            ->orderBy('id')
            ->each(function (object $individual) use ($db): void {
                $lastAliveDate = $db->table('seedling_experiment_records')
                    ->where('individual_id', $individual->id)
                    ->where('status', 'A')
                    ->where('survey_date', '<=', $individual->end_date)
                    ->max('survey_date');

                $firstDeadDate = $db->table('seedling_experiment_records')
                    ->where('individual_id', $individual->id)
                    ->where('status', 'D')
                    ->when($lastAliveDate, fn ($query) => $query->where('survey_date', '>', $lastAliveDate))
                    ->where('survey_date', '<=', $individual->end_date)
                    ->min('survey_date');

                $db->table('seedling_experiment_individuals')->where('id', $individual->id)->update([
                    'death_date' => $firstDeadDate ?: $individual->end_date,
                ]);
            });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('seedling_experiment_individuals', function (Blueprint $table): void {
            $table->dropIndex(['death_date']);
            $table->dropColumn('death_date');
        });
    }
};
