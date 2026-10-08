<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PageRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateForAdmin(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): ?Page;
}
