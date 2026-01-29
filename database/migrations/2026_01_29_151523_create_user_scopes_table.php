<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_scopes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();

            // 你說「審核時才開放研究項目」→ 這裡用 is_enabled 控制
            $table->boolean('is_enabled')->default(true);

            // 可選：之後要做更細權限（先留著不會影響你現在使用）
            $table->boolean('can_view')->default(true);
            $table->boolean('can_create')->default(true);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);

            // 這筆 scope 是誰核准的（可選，但通常很有用）
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // 一個人對同一 site+module 只能有一筆
            $table->unique(['user_id', 'site_id', 'module_id']);

            $table->index(['site_id', 'module_id']);
            $table->index(['user_id', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_scopes');
    }
};
