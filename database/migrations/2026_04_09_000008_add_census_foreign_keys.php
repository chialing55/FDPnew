<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->table('teams', function (Blueprint $table) {
            $table->foreign('census')
                ->references('census')
                ->on('censuses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::connection('fs_mortality')->table('census_records', function (Blueprint $table) {
            $table->foreign('census')
                ->references('census')
                ->on('censuses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->table('census_records', function (Blueprint $table) {
            $table->dropForeign(['census']);
        });

        Schema::connection('fs_mortality')->table('teams', function (Blueprint $table) {
            $table->dropForeign(['census']);
        });
    }
};
