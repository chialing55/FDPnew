<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('mysql_web')->hasColumn('content_blocks', 'attachments')) {
            Schema::connection('mysql_web')->table('content_blocks', function (Blueprint $table): void {
                $table->dropColumn('attachments');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::connection('mysql_web')->hasColumn('content_blocks', 'attachments')) {
            Schema::connection('mysql_web')->table('content_blocks', function (Blueprint $table): void {
                $table->json('attachments')->nullable();
            });
        }
    }
};
