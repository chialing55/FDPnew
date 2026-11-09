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
            $table->boolean('is_backfilled')->default(false)->after('note');
        });

        Schema::connection($this->connection)->table('seedling_experiment_individuals', function (Blueprint $table): void {
            $table->dropIndex(['end_reason']);
            $table->dropColumn('end_reason');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('seedling_experiment_individuals', function (Blueprint $table): void {
            $table->string('end_reason')->nullable()->index()->after('end_date');
        });

        Schema::connection($this->connection)->table('seedling_experiment_records', function (Blueprint $table): void {
            $table->dropColumn('is_backfilled');
        });
    }
};
