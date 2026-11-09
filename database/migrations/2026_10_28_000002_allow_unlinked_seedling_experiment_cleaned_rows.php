<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        $tables = [
            'seedling_groundcover_phase1_cleaned' => 'phase1_cleaned_source_index',
            'seedling_groundcover_phase2_cleaned' => 'phase2_cleaned_source_index',
        ];

        foreach ($tables as $tableName => $indexName) {
            $schema = Schema::connection($this->connection);
            $hasSourceFile = $schema->hasColumn($tableName, 'source_file');
            $hasSourceSheet = $schema->hasColumn($tableName, 'source_sheet');
            $hasSourceRow = $schema->hasColumn($tableName, 'source_row');

            $schema->table($tableName, function (Blueprint $table) use ($hasSourceFile, $hasSourceSheet, $hasSourceRow, $indexName): void {
                $table->unsignedBigInteger('raw_id')->nullable()->change();
                if (!$hasSourceFile) {
                    $table->string('source_file')->after('clean_note');
                }
                if (!$hasSourceSheet) {
                    $table->string('source_sheet')->nullable()->after('source_file');
                }
                if (!$hasSourceRow) {
                    $table->unsignedInteger('source_row')->nullable()->after('source_sheet');
                }
                $table->index(['source_file', 'source_sheet', 'source_row'], $indexName);
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'seedling_groundcover_phase1_cleaned' => 'phase1_cleaned_source_index',
            'seedling_groundcover_phase2_cleaned' => 'phase2_cleaned_source_index',
        ];

        foreach ($tables as $tableName => $indexName) {
            Schema::connection($this->connection)->table($tableName, function (Blueprint $table) use ($indexName): void {
                $table->dropIndex($indexName);
                $table->dropColumn(['source_file', 'source_sheet', 'source_row']);
                $table->unsignedBigInteger('raw_id')->nullable(false)->change();
            });
        }
    }
};
