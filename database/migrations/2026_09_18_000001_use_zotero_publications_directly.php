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

        if ($schema->hasColumn('publications', 'external_id') && ! $schema->hasColumn('publications', 'zotero_id')) {
            $schema->table('publications', function (Blueprint $table): void {
                $table->renameColumn('external_id', 'zotero_id');
            });
        }

        if (! $schema->hasColumn('publications', 'site_review_status')) {
            $schema->table('publications', function (Blueprint $table): void {
                $table->string('site_review_status', 20)->default('reviewed')->index()->after('is_changyang');
            });
        }

        DB::connection($this->connection)->table('publications')->update(['is_changyang' => true, 'site_review_status' => 'reviewed']);
        $schema->dropIfExists('publication_candidates');
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);
        if ($schema->hasColumn('publications', 'site_review_status')) {
            $schema->table('publications', function (Blueprint $table): void {
                $table->dropColumn('site_review_status');
            });
        }
        if ($schema->hasColumn('publications', 'zotero_id') && ! $schema->hasColumn('publications', 'external_id')) {
            $schema->table('publications', function (Blueprint $table): void {
                $table->renameColumn('zotero_id', 'external_id');
            });
        }
    }
};
