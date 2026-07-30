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
            $table->string('authors_zh_tw', 1000)->nullable()->after('authors');
            $table->string('title_zh_tw', 500)->nullable()->after('title');
            $table->string('journal_zh_tw')->nullable()->after('journal');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('publications', function (Blueprint $table): void {
            $table->dropColumn(['authors_zh_tw', 'title_zh_tw', 'journal_zh_tw']);
        });
    }
};
