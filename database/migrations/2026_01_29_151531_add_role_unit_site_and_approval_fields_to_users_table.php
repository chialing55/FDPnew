<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 角色：data_manager / pi / ra
            $table->string('role', 30)->default('ra')->after('password');

            // 單位（你想簡單就先用字串；之後要正規化再改成 unit_id）
            $table->string('unit', 100)->nullable()->after('role');

            // 註冊時選擇的樣區（單選）
            $table->foreignId('site_id')
                ->nullable()
                ->after('unit')
                ->constrained('sites')
                ->nullOnDelete();

            // // 審核欄位（通過才可進輸入系統）
            // $table->timestamp('approved_at')->nullable()->after('site_id');
            // $table->foreignId('approved_by')
            //     ->nullable()
            //     ->after('approved_at')
            //     ->constrained('users')
            //     ->nullOnDelete();

            // 是否可進 Filament：依你需求只有資料管理員可進
            // 這欄位不是必要，但留著有彈性（例如未來你想讓 PI 也能進部分後台）
            $table->boolean('can_access_filament')->default(false)->after('approved_by');

            // 常用索引
            $table->index(['role', 'approved_at']);
            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'approved_at']);
            $table->dropIndex(['site_id']);

            $table->dropColumn(['can_access_filament', 'approved_at', 'role', 'unit']);

            // 注意 drop foreign key 欄位順序
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('site_id');
        });
    }
};
