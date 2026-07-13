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
            $table->string('authors', 1000)->nullable()->after('year');
            $table->string('title', 500)->nullable()->after('authors');
            $table->string('journal')->nullable()->after('title');
            $table->string('citation_style', 30)->default('year_after_authors')->after('pages');
            $table->string('pdf_path')->nullable()->after('url');
            $table->dropColumn(['authors_zh_tw', 'authors_en', 'title_zh_tw', 'title_en', 'journal_zh_tw', 'journal_en']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('publications', function (Blueprint $table): void {
            $table->string('authors_zh_tw', 1000)->nullable();
            $table->string('authors_en', 1000)->nullable();
            $table->string('title_zh_tw')->nullable();
            $table->string('title_en')->nullable();
            $table->string('journal_zh_tw')->nullable();
            $table->string('journal_en')->nullable();
            $table->dropColumn(['authors', 'title', 'journal', 'citation_style', 'pdf_path']);
        });
    }
};
