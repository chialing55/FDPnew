<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('mysql_web')->hasColumn('publications', 'type')) {
            Schema::connection('mysql_web')->table('publications', function (Blueprint $table): void {
                $table->string('type', 50)->default('paper')->after('title')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::connection('mysql_web')->hasColumn('publications', 'type')) {
            Schema::connection('mysql_web')->table('publications', function (Blueprint $table): void {
                $table->dropIndex(['type']);
                $table->dropColumn('type');
            });
        }
    }
};
