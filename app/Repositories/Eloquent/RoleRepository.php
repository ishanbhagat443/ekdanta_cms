<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function allRoles(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }

    public function paginateWithCounts(int $perPage = 15): LengthAwarePaginator
    {
        return Role::query()
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findRole(int $id): Role
    {
        return Role::query()->findOrFail($id);
    }

    public function createRole(string $name): Role
    {
        return Role::create(['name' => $name, 'guard_name' => 'web']);
    }

    public function renameRole(Role $role, string $name): Role
    {
        $role->update(['name' => $name]);

        return $role->refresh();
    }

    public function deleteRole(Role $role): bool
    {
        return (bool) $role->delete();
    }
}
