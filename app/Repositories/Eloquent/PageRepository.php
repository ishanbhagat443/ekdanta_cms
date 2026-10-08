<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Page;
use App\Repositories\Contracts\PageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageRepository extends BaseRepository implements PageRepositoryInterface
{
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    public function paginateForAdmin(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->query()
            ->withCount('sections')
            ->when($search, fn ($q, string $term) => $q->where('title', 'like', "%{$term}%"))
            ->orderBy('title')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?Page
    {
        /** @var Page|null */
        return $this->query()
            ->where('slug', $slug)
            ->where('status', Page::STATUS_PUBLISHED)
            ->with(['sections' => fn ($q) => $q->where('is_active', true), 'bannerMedia'])
            ->first();
    }
}
