<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('mysql_web')->hasColumn('sites', 'homepage_image_position')) {
            Schema::connection('mysql_web')->table('sites', function (Blueprint $table): void {
                $table->unsignedTinyInteger('homepage_image_position')
                    ->default(50)
                    ->after('homepage_image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::connection('mysql_web')->hasColumn('sites', 'homepage_image_position')) {
            Schema::connection('mysql_web')->table('sites', function (Blueprint $table): void {
                $table->dropColumn('homepage_image_position');
            });
        }
    }
};
