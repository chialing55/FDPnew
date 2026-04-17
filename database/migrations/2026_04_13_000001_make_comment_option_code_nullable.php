<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->table('comment_options', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->table('comment_options', function (Blueprint $table) {
            $table->string('code', 50)->nullable(false)->change();
        });
    }
};
