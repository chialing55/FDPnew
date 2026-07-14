<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('mysql_web')->hasColumn('subjects', 'is_active')) {
            Schema::connection('mysql_web')->table('subjects', function (Blueprint $table): void {
                $table->boolean('is_active')->default(true)->index();
            });
        }

        DB::connection('mysql_web')
            ->table('subjects')
            ->update(['is_active' => true]);
    }

    public function down(): void
    {
        // Existing publication states cannot be reconstructed safely.
    }
};
