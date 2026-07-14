<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'description_zh_tw',
        'description_en',
        'method_zh_tw',
        'method_en',
    ];

    public function up(): void
    {
        $columns = array_values(array_filter(
            self::COLUMNS,
            fn (string $column): bool => Schema::connection('mysql_web')->hasColumn('subjects', $column),
        ));

        if ($columns !== []) {
            Schema::connection('mysql_web')->table('subjects', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('mysql_web')->table('subjects', function (Blueprint $table): void {
            $table->text('description_zh_tw')->nullable();
            $table->text('description_en')->nullable();
            $table->longText('method_zh_tw')->nullable();
            $table->longText('method_en')->nullable();
        });
    }
};
