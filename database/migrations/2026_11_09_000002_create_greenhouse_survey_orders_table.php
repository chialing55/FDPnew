<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        Schema::connection($this->connection)->create('greenhouse_survey_orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('survey_order')->index();
            $table->string('shade')->nullable()->index();
            $table->string('date')->nullable();
            $table->string('species')->nullable();
            $table->string('plot')->index();
            $table->string('no');
            $table->string('length')->nullable();
            $table->string('leaf_count')->nullable();
            $table->string('status')->nullable();
            $table->text('note')->nullable();
            $table->string('source_file');
            $table->string('source_sheet')->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->timestamps();

            $table->unique(['plot', 'no'], 'greenhouse_survey_orders_individual_unique');
            $table->unique('survey_order', 'greenhouse_survey_orders_order_unique');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('greenhouse_survey_orders');
    }
};
