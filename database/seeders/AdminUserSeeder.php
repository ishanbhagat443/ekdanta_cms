<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'admin@ekdanta.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // change after first login
                'is_active' => true,
            ]
        );

        $user->syncRoles([config('gsm.roles.super_admin')]);
    }
}
