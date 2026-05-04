<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_web')->table('photo', function (Blueprint $table) {
            if (! Schema::connection('mysql_web')->hasColumn('photo', 'photo_date')) {
                $table->date('photo_date')->nullable()->after('filename');
            }

            if (! Schema::connection('mysql_web')->hasColumn('photo', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('photo_date');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_web')->table('photo', function (Blueprint $table) {
            if (Schema::connection('mysql_web')->hasColumn('photo', 'is_public')) {
                $table->dropColumn('is_public');
            }

            if (Schema::connection('mysql_web')->hasColumn('photo', 'photo_date')) {
                $table->dropColumn('photo_date');
            }
        });
    }
};
