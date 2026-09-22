<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_web')->table('changyang_block_images', function (Blueprint $table): void {
            $table->string('photographer')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_web')->table('changyang_block_images', function (Blueprint $table): void {
            $table->dropColumn('photographer');
        });
    }
};
