<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->create('tree_individuals', function (Blueprint $table) {
            $table->id();
            $table->string('stemid', 50)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->dropIfExists('tree_individuals');
    }
};
