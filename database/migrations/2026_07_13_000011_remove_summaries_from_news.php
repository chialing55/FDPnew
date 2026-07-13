<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        Schema::connection($this->connection)->table('news', function (Blueprint $table): void {
            $table->dropColumn(['summary_zh_tw', 'summary_en']);
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
