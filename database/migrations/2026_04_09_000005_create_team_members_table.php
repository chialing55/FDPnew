<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('role', 50)->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->dropIfExists('team_members');
    }
};
