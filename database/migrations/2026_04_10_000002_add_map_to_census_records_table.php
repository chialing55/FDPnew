<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->table('census_records', function (Blueprint $table) {
            $table->string('map', 50)->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->table('census_records', function (Blueprint $table) {
            $table->dropColumn('map');
        });
    }
};
