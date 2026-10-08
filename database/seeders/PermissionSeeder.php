<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Permission → roles map. Super Admin needs no rows (Gate::before bypass).
     * Module permissions are appended here as each module is built.
     *
     * @var array<string, list<string>>
     */
    private array $map = [
        'settings.manage' => ['Admin'],
        'media.manage' => ['Admin', 'Content Manager', 'Marketing', 'SEO Manager'],
        'builder.header' => ['Admin', 'Content Manager'],
        'builder.footer' => ['Admin', 'Content Manager'],
        'pages.manage' => ['Admin', 'Content Manager'],
        'blog.manage' => ['Admin', 'Content Manager', 'Marketing'],
        'forms.manage' => ['Admin', 'Content Manager', 'Marketing'],
        'enquiries.manage' => ['Admin', 'Content Manager', 'Marketing'],
        'menus.manage' => ['Admin', 'Content Manager'],
        'seo.manage' => ['Admin', 'SEO Manager'],
        'theme.manage' => ['Admin'],
        'users.view' => ['Admin'],
        'users.create' => ['Admin'],
        'users.update' => ['Admin'],
        'users.delete' => [],
        'roles.manage' => [],
        'activity.view' => ['Admin'],
        'blocks.manage' => ['Admin', 'Content Manager'],
        // Developer Mode: granted to NO role — only Super Admin (Gate::before
        // bypass) can see the button or hit the endpoints.
        'developer.mode' => [],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->map as $permission => $roleNames) {
            $perm = Permission::findOrCreate($permission, 'web');

            foreach ($roleNames as $roleName) {
                Role::findOrCreate($roleName, 'web')->givePermissionTo($perm);
            }
        }
    }
}
