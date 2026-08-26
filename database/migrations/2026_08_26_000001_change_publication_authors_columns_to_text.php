<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        Schema::connection($this->connection)->table('publications', function (Blueprint $table): void {
            $table->text('authors')->nullable()->change();
            $table->text('authors_zh_tw')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Shrinking TEXT back to VARCHAR(1000) could truncate restored/imported
        // author lists, so rollback intentionally preserves the wider columns.
    }
};
