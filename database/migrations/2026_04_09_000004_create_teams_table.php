<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->create('teams', function (Blueprint $table) {
            $table->id();
            $table->integer('census')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->dropIfExists('teams');
    }
};
