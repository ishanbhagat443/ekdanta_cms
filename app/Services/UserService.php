<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(protected UserRepositoryInterface $users)
    {
    }

    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            /** @var User $user */
            $user = $this->users->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $user->syncRoles([$data['role']]);

            return $user;
        });
    }

    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ];

            if (! empty($data['password'])) {
                $attributes['password'] = Hash::make($data['password']);
            }

            $this->users->update($user, $attributes);
            $user->syncRoles([$data['role']]);

            return $user->refresh();
        });
    }

    public function deleteUser(User $user): void
    {
        $this->users->delete($user);
    }

    public function updateProfile(User $user, array $data): User
    {
        return $this->users->update($user, [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    public function changePassword(User $user, string $newPassword): void
    {
        $this->users->update($user, ['password' => Hash::make($newPassword)]);
    }
}
