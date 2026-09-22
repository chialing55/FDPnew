<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);
        $schema->table('publications', function (Blueprint $table) use ($schema): void {
            if (! $schema->hasColumn('publications', 'authors_zh_tw')) {
                $table->string('authors_zh_tw', 1000)->nullable()->after('year');
            }
            if (! $schema->hasColumn('publications', 'authors_en')) {
                $table->string('authors_en', 1000)->nullable()->after('authors_zh_tw');
            }
            $legacy = array_values(array_filter(['citation_zh_tw', 'citation_en'], fn (string $column): bool => $schema->hasColumn('publications', $column)));
            if ($legacy !== []) {
                $table->dropColumn($legacy);
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('publications', function (Blueprint $table): void {
            $table->longText('citation_zh_tw')->nullable();
            $table->longText('citation_en')->nullable();
            $table->dropColumn(['authors_zh_tw', 'authors_en']);
        });
    }
};
