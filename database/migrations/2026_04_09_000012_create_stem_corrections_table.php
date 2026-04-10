<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->create('stem_corrections', function (Blueprint $table) {
            $table->id();
            $table->string('stemid', 50)->index();
            $table->unsignedBigInteger('census_record_id')->nullable()->index();
            $table->string('correction_type', 50);
            $table->string('field_name', 50);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
            $table->string('created_by', 50)->nullable();
            $table->string('updated_by', 50)->nullable();

            $table->foreign('stemid')
                ->references('stemid')
                ->on('tree_individuals')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('census_record_id')
                ->references('id')
                ->on('census_records')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->dropIfExists('stem_corrections');
    }
};
