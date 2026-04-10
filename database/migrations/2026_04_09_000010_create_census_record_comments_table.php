<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->create('census_record_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('census_record_id')->constrained('census_records')->cascadeOnDelete();
            $table->foreignId('comment_option_id')->nullable()->constrained('comment_options')->nullOnDelete();
            $table->integer('sort_order')->nullable();
            $table->text('comment_other')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->dropIfExists('census_record_comments');
    }
};
