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

        $schema->create('greenhouse_raw', function (Blueprint $table): void {
            $table->id();
            $table->string('survey_order')->nullable();
            $table->string('shade')->nullable();
            $table->string('date')->nullable();
            $table->string('species')->nullable();
            $table->string('plot')->nullable()->index();
            $table->string('no')->nullable();
            $table->string('length')->nullable();
            $table->string('leaf_count')->nullable();
            $table->string('status')->nullable();
            $table->text('note')->nullable();
            $table->string('source_file');
            $table->string('source_sheet')->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->timestamps();

            $table->index(['source_file', 'source_sheet', 'source_row'], 'greenhouse_raw_source_index');
        });

        $schema->create('greenhouse_cleaned', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('raw_id')->constrained('greenhouse_raw')->cascadeOnDelete();
            $table->unsignedInteger('survey_order')->nullable()->index();
            $table->string('shade')->nullable()->index();
            $table->date('survey_date')->nullable()->index();
            $table->string('plot')->nullable()->index();
            $table->string('individual_no')->nullable();
            $table->string('species')->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->integer('leaf_count')->nullable();
            $table->string('status')->nullable()->index();
            $table->text('note')->nullable();
            $table->text('clean_note')->nullable();
            $table->string('source_file');
            $table->string('source_sheet')->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->timestamps();

            $table->index(['plot', 'individual_no'], 'greenhouse_cleaned_individual_index');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);
        $schema->dropIfExists('greenhouse_cleaned');
        $schema->dropIfExists('greenhouse_raw');
    }
};
