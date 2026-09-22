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

        if (! $schema->hasColumn('news', 'slug')) {
            return;
        }

        $hasSlugIndex = DB::connection($this->connection)
            ->table('information_schema.statistics')
            ->where('table_schema', DB::connection($this->connection)->getDatabaseName())
            ->where('table_name', 'news')
            ->where('index_name', 'slug')
            ->exists();

        Schema::connection($this->connection)->table('news', function (Blueprint $table) use ($hasSlugIndex): void {
            if ($hasSlugIndex) {
                $table->dropUnique('slug');
            }

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
