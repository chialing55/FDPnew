<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'fs_seedling_exp';

    public function up(): void
    {
        Schema::connection($this->connection)->table('plots', function (Blueprint $table): void {
            $table->unsignedInteger('density')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('plots', function (Blueprint $table): void {
            $table->decimal('density', 10, 3)->nullable()->change();
        });
    }
};
