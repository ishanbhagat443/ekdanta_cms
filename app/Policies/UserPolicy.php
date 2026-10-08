<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        // Nobody edits a Super Admin except a Super Admin (Gate::before covers that).
        if ($model->hasRole(config('gsm.roles.super_admin'))) {
            return false;
        }

        return $user->can('users.update');
    }

    public function delete(User $user, User $model): bool
    {
        // No self-delete, no deleting Super Admins.
        if ($user->is($model) || $model->hasRole(config('gsm.roles.super_admin'))) {
            return false;
        }

        return $user->can('users.delete');
    }
}
