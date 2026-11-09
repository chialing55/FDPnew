<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        $schema->table('seedling_groundcover_phase2_raw', function (Blueprint $table): void {
            $table->unsignedInteger('survey_order')->nullable()->after('id')->index();
        });

        $schema->table('seedling_groundcover_phase2_cleaned', function (Blueprint $table): void {
            $table->unsignedInteger('survey_order')->nullable()->after('raw_id')->index();
        });

    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);
        $schema->table('seedling_groundcover_phase2_cleaned', function (Blueprint $table): void {
            $table->dropColumn('survey_order');
        });

        $schema->table('seedling_groundcover_phase2_raw', function (Blueprint $table): void {
            $table->dropColumn('survey_order');
        });
    }
};
