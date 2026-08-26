<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        Schema::connection($this->connection)->table('publications', function (Blueprint $table): void {
            $table->string('language', 10)->default('en')->after('type');
            $table->string('institution')->nullable()->after('journal_zh_tw');
            $table->string('institution_zh_tw')->nullable()->after('institution');
            $table->string('thesis_type', 20)->nullable()->after('institution_zh_tw');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('publications', function (Blueprint $table): void {
            $table->dropColumn(['language', 'institution', 'institution_zh_tw', 'thesis_type']);
        });
    }
};
