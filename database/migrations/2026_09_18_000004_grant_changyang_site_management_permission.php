<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::findOrCreate('manage-changyang-site', 'web');

        User::query()
            ->whereIn('account', ['chialing', 'choyo'])
            ->each(fn (User $user) => $user->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'manage-changyang-site')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
