<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(protected RoleRepositoryInterface $roles)
    {
    }

    public function createRole(string $name, array $permissions): Role
    {
        return DB::transaction(function () use ($name, $permissions): Role {
            $role = $this->roles->createRole($name);
            $role->syncPermissions($permissions);

            return $role;
        });
    }

    public function updateRole(Role $role, string $name, array $permissions): Role
    {
        $this->guardSuperAdmin($role);

        return DB::transaction(function () use ($role, $name, $permissions): Role {
            $role = $this->roles->renameRole($role, $name);
            $role->syncPermissions($permissions);

            return $role;
        });
    }

    public function deleteRole(Role $role): void
    {
        $this->guardSuperAdmin($role);

        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Cannot delete a role that is assigned to users. Reassign those users first.',
            ]);
        }

        $this->roles->deleteRole($role);
    }

    /**
     * Permissions grouped by module prefix for the matrix UI.
     *
     * @return array<string, \Illuminate\Support\Collection<int, Permission>>
     */
    public function permissionMatrix(): array
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $p) => explode('.', $p->name)[0])
            ->sortKeys()
            ->all();
    }

    private function guardSuperAdmin(Role $role): void
    {
        if ($role->name === config('gsm.roles.super_admin')) {
            throw ValidationException::withMessages([
                'role' => 'The Super Admin role cannot be modified or deleted.',
            ]);
        }
    }
}
