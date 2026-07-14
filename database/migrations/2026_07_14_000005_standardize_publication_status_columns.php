<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['research_outputs', 'news'] as $tableName) {
            if (
                Schema::connection('mysql_web')->hasColumn($tableName, 'is_public')
                && ! Schema::connection('mysql_web')->hasColumn($tableName, 'is_active')
            ) {
                Schema::connection('mysql_web')->table($tableName, function (Blueprint $table): void {
                    $table->renameColumn('is_public', 'is_active');
                });
            }
        }

        if (! Schema::connection('mysql_web')->hasColumn('publications', 'is_active')) {
            Schema::connection('mysql_web')->table('publications', function (Blueprint $table): void {
                $table->boolean('is_active')->default(true)->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::connection('mysql_web')->hasColumn('publications', 'is_active')) {
            Schema::connection('mysql_web')->table('publications', function (Blueprint $table): void {
                $table->dropColumn('is_active');
            });
        }

        foreach (['research_outputs', 'news'] as $tableName) {
            if (
                Schema::connection('mysql_web')->hasColumn($tableName, 'is_active')
                && ! Schema::connection('mysql_web')->hasColumn($tableName, 'is_public')
            ) {
                Schema::connection('mysql_web')->table($tableName, function (Blueprint $table): void {
                    $table->renameColumn('is_active', 'is_public');
                });
            }
        }
    }
};
