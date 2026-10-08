<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Media;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MediaRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Browse media with optional folder scope, search term and type filter.
     * $folderId null = root; 'all' handled by $anyFolder flag.
     */
    public function browse(
        ?int $folderId,
        ?string $search,
        ?string $type,
        bool $anyFolder,
        int $perPage
    ): LengthAwarePaginator;

    /** @return Collection<int, Media> ordered to match the given IDs */
    public function byIdsOrdered(array $ids): Collection;
}
