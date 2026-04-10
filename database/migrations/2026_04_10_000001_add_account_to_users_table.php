<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account', 50)->nullable()->after('name');
        });

        DB::table('users')->whereNull('account')->update([
            'account' => DB::raw('email'),
        ]);

        DB::statement('ALTER TABLE users MODIFY account VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE users ADD UNIQUE users_account_unique (account)');
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_account_unique');
            $table->dropColumn('account');
        });
    }
};
