<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('mysql_web')->hasColumn('sites', 'plot_area_ha')) {
            Schema::connection('mysql_web')->table('sites', function (Blueprint $table): void {
                $table->decimal('plot_area_ha', 10, 2)->nullable()->after('elevation_m');
            });
        }

        if (! Schema::connection('mysql_web')->hasColumn('sites', 'established_year')) {
            Schema::connection('mysql_web')->table('sites', function (Blueprint $table): void {
                $table->unsignedSmallInteger('established_year')->nullable()->after('plot_area_ha');
            });
        }
    }

    public function down(): void
    {
        if (Schema::connection('mysql_web')->hasColumn('sites', 'established_year')) {
            Schema::connection('mysql_web')->table('sites', function (Blueprint $table): void {
                $table->dropColumn('established_year');
            });
        }

        if (Schema::connection('mysql_web')->hasColumn('sites', 'plot_area_ha')) {
            Schema::connection('mysql_web')->table('sites', function (Blueprint $table): void {
                $table->dropColumn('plot_area_ha');
            });
        }
    }
};
