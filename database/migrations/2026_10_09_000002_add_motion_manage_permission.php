<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** "motion.manage": the Motion Library. Admins get it, as with the other Design screens. */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::findOrCreate('motion.manage', 'web');

        if ($admin = Role::query()->where('name', 'Admin')->where('guard_name', 'web')->first()) {
            $admin->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        Permission::query()->where('name', 'motion.manage')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
