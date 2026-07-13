<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        Schema::connection($this->connection)->table('news', function (Blueprint $table): void {
            $table->dropUnique('slug');
            $table->dropColumn('slug');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('news', function (Blueprint $table): void {
            $table->string('slug', 150)->nullable()->unique()->after('id');
        });
    }
};
