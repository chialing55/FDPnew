<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_web')->table('photo', function (Blueprint $table): void {
            if (! Schema::connection('mysql_web')->hasColumn('photo', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_public')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_web')->table('photo', function (Blueprint $table): void {
            if (Schema::connection('mysql_web')->hasColumn('photo', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
        });
    }
};
