<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        Schema::connection($this->connection)->table('research_outputs', function (Blueprint $table): void {
            $table->dropColumn(['body_zh_tw', 'body_en', 'view', 'params']);
        });
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
