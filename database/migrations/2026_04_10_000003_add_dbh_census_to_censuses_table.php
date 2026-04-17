<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->table('censuses', function (Blueprint $table) {
            $table->string('dbh_census', 50)->nullable()->after('has_dbh');
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->table('censuses', function (Blueprint $table) {
            $table->dropColumn('dbh_census');
        });
    }
};
