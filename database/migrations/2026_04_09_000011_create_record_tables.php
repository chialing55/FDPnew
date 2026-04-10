<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        $this->createRecordTable('record_1');
        $this->createRecordTable('record_2');
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->dropIfExists('record_2');
        Schema::connection('fs_mortality')->dropIfExists('record_1');
    }

    private function createRecordTable(string $tableName): void
    {
        Schema::connection('fs_mortality')->create($tableName, function (Blueprint $table) {
            $table->id();
            $table->integer('census')->index();
            $table->string('map', 50);
            $table->integer('qx');
            $table->integer('qy');
            $table->integer('subqx');
            $table->integer('subqy');
            $table->string('stemid', 50)->index();
            $table->string('csp', 50);
            $table->decimal('x', 6, 2);
            $table->decimal('y', 6, 2);
            $table->decimal('dbh1', 6, 2)->nullable();
            $table->decimal('dbh2', 6, 2)->nullable();
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
            $table->json('comments_json')->nullable();
            $table->json('stem_corrections_json')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('team_id')->index();
            $table->timestamps();
            $table->string('created_by', 50)->nullable();
            $table->string('updated_by', 50)->nullable();

            $table->index(['qx', 'qy']);
            $table->index(['subqx', 'subqy']);
            $table->foreign('census')->references('census')->on('censuses')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('stemid')->references('stemid')->on('tree_individuals')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnUpdate()->restrictOnDelete();
        });
    }
};
