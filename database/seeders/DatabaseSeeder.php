<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed a blank CMS: roles, permissions, the admin user, settings, the
     * design system, plus a starter header/footer and an empty Home page.
     * No demo content — every page is built from the admin panel.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            AdminUserSeeder::class,
            SettingSeeder::class,
            ThemeSeeder::class,
            DesignSystemSeeder::class,
            GlobalBlockCategorySeeder::class,
            FormSeeder::class,
            HeaderSeeder::class,
            FooterSeeder::class,
            PageSeeder::class,
        ]);
    }
}
