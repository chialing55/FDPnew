<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->create('census_records', function (Blueprint $table) {
            $table->id();
            $table->string('stemid', 50)->index();
            $table->integer('census')->index();
            $table->date('date');
            $table->decimal('dbh', 6, 2)->nullable();
            $table->string('status', 20);
            $table->string('mode', 50)->nullable();
            $table->decimal('living_length', 6, 2)->nullable();
            $table->integer('branches')->nullable();
            $table->integer('illumination')->nullable();
            $table->integer('leaning')->nullable();
            $table->string('liana', 50)->nullable();
            $table->boolean('fungi')->nullable();
            $table->tinyInteger('wounded_stem')->nullable();
            $table->tinyInteger('deformity')->nullable();
            $table->tinyInteger('rotten')->nullable();
            $table->integer('leaves')->nullable();
            $table->boolean('leaf_damage')->nullable();
            $table->unsignedBigInteger('team_id')->index();
            $table->timestamps();
            $table->string('created_by', 50)->nullable();
            $table->string('updated_by', 50)->nullable();

            $table->unique(['stemid', 'census']);
            $table->foreign('stemid')->references('stemid')->on('tree_individuals')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->dropIfExists('census_records');
    }
};
