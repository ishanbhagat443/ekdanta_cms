<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    public function allRoles(): Collection;

    public function paginateWithCounts(int $perPage = 15): LengthAwarePaginator;

    public function findRole(int $id): Role;

    public function createRole(string $name): Role;

    public function renameRole(Role $role, string $name): Role;

    public function deleteRole(Role $role): bool;
}
