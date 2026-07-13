<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);
        $db = DB::connection($this->connection);

        $schema->create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $citationStyle = $db->table('publications')->whereNotNull('citation_style')->value('citation_style');
        if (! in_array($citationStyle, ['year_after_authors', 'year_at_end'], true)) {
            $citationStyle = 'year_after_authors';
        }

        $db->table('site_settings')->insert([
            'key' => 'publication_citation_style',
            'value' => $citationStyle,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $schema->table('publications', function (Blueprint $table): void {
            $table->dropColumn('citation_style');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);
        $db = DB::connection($this->connection);
        $citationStyle = $db->table('site_settings')
            ->where('key', 'publication_citation_style')
            ->value('value') ?? 'year_after_authors';

        $schema->table('publications', function (Blueprint $table): void {
            $table->string('citation_style', 30)->default('year_after_authors')->after('pages');
        });

        $db->table('publications')->update(['citation_style' => $citationStyle]);
        $schema->dropIfExists('site_settings');
    }
};
