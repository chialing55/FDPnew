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
        $columns = array_values(array_filter(
            ['summary_zh_tw', 'summary_en'],
            fn (string $column): bool => $schema->hasColumn('projects', $column),
        ));

        if ($columns !== []) {
            $schema->table('projects', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('projects', function (Blueprint $table): void {
            $table->longText('summary_zh_tw')->nullable();
            $table->longText('summary_en')->nullable();
        });
    }
};
