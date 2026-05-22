<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql3')->table('seedling_records', function (Blueprint $table) {
            if (!Schema::connection('mysql3')->hasColumn('seedling_records', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            }
        });

        Schema::connection('mysql3')->table('seedling_individuals', function (Blueprint $table) {
            if (!Schema::connection('mysql3')->hasColumn('seedling_individuals', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            }

            if (!Schema::connection('mysql3')->hasColumn('seedling_individuals', 'merge_to')) {
                $table->string('merge_to', 20)->nullable()->after('mtag');
            }
        });

        Schema::connection('mysql3')->table('seedling_stems', function (Blueprint $table) {
            if (!Schema::connection('mysql3')->hasColumn('seedling_stems', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('mysql3')->table('seedling_records', function (Blueprint $table) {
            if (Schema::connection('mysql3')->hasColumn('seedling_records', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });

        Schema::connection('mysql3')->table('seedling_individuals', function (Blueprint $table) {
            if (Schema::connection('mysql3')->hasColumn('seedling_individuals', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }

            if (Schema::connection('mysql3')->hasColumn('seedling_individuals', 'merge_to')) {
                $table->dropColumn('merge_to');
            }
        });

        Schema::connection('mysql3')->table('seedling_stems', function (Blueprint $table) {
            if (Schema::connection('mysql3')->hasColumn('seedling_stems', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};
