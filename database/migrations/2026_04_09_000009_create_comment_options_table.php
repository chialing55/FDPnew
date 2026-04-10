<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->create('comment_options', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('comment_zh', 255);
            $table->string('comment_en', 255);
            $table->string('category', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->dropIfExists('comment_options');
    }
};
