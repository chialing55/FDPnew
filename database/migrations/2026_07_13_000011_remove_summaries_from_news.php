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
        $columns = collect(['summary_zh_tw', 'summary_en'])
            ->filter(fn (string $column): bool => $schema->hasColumn('news', $column))
            ->all();

        if ($columns === []) {
            return;
        }

        $schema->table('news', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('news', function (Blueprint $table): void {
            $table->text('summary_zh_tw')->nullable();
            $table->text('summary_en')->nullable();
        });
    }
};
