<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('mysql_web')->hasColumn('pages', 'view')) {
            Schema::connection('mysql_web')->table('pages', function (Blueprint $table): void {
                $table->dropColumn('view');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::connection('mysql_web')->hasColumn('pages', 'view')) {
            Schema::connection('mysql_web')->table('pages', function (Blueprint $table): void {
                $table->string('view', 150)->nullable();
            });
        }
    }
};
