<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithRoles(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    public function findByEmail(string $email): ?User;
}
