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
            ['body_zh_tw', 'body_en', 'view', 'params'],
            fn (string $column): bool => $schema->hasColumn('research_outputs', $column),
        ));

        if ($columns !== []) {
            $schema->table('research_outputs', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('research_outputs', function (Blueprint $table): void {
            $table->longText('body_zh_tw')->nullable();
            $table->longText('body_en')->nullable();
            $table->string('view')->nullable();
            $table->json('params')->nullable();
        });
    }
};
