<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->table('census_records', function (Blueprint $table) {
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->table('census_records', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });
    }
};
