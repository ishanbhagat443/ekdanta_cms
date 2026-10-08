<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\GlobalBlock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface GlobalBlockRepositoryInterface
{
    /**
     * Library listing: search/filter/sort. $filters keys: search, category,
     * type, favorites, sort (recent|most_used|name|newest).
     */
    public function paginateForAdmin(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function findBySlug(string $slug): ?GlobalBlock;

    /** Options for the "Add Existing Block" picker in the Page Builder. */
    public function activeOptions(): Collection;

    public function create(array $attributes): GlobalBlock;

    public function update(GlobalBlock $block, array $attributes): GlobalBlock;

    public function delete(GlobalBlock $block): void;
}
