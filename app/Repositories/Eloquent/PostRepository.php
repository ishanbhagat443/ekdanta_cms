<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function paginateForAdmin(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return $this->query()
            ->with(['featuredMedia', 'categories:id,name', 'author:id,name'])
            ->when($search, fn ($q, string $term) => $q->where('title', 'like', "%{$term}%"))
            ->when($status, fn ($q, string $s) => $q->where('status', $s))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateLive(array $filters, int $perPage): LengthAwarePaginator
    {
        // 'sort' and 'featured_first' are optional (Blog Listing CMS grid
        // settings) — omitting them preserves the exact original behavior
        // (orderByDesc('published_at') only).
        $sort = $filters['sort'] ?? 'latest';
        $featuredFirst = (bool) ($filters['featured_first'] ?? false);

        return $this->liveQuery()
            ->with(['featuredMedia', 'categories:id,name,slug', 'author:id,name'])
            ->when($filters['search'] ?? null, function ($q, string $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('body', 'like', "%{$term}%");
                });
            })
            ->when($filters['category'] ?? null, function ($q, string $slug) {
                $q->whereHas('categories', fn ($c) => $c->where('slug', $slug));
            })
            ->when($filters['tag'] ?? null, function ($q, string $slug) {
                $q->whereHas('tags', fn ($t) => $t->where('slug', $slug));
            })
            ->when($filters['archive'] ?? null, function ($q, string $month) {
                // "2026-07"
                [$y, $m] = array_pad(explode('-', $month), 2, null);
                if (is_numeric($y) && is_numeric($m)) {
                    $q->whereYear('published_at', (int) $y)->whereMonth('published_at', (int) $m);
                }
            })
            ->when($featuredFirst, fn ($q) => $q->orderByDesc('is_featured'))
            ->when($sort === 'oldest', fn ($q) => $q->orderBy('published_at'))
            ->when($sort === 'popular', fn ($q) => $q->orderByDesc('views_count'))
            ->when($sort === 'latest', fn ($q) => $q->orderByDesc('published_at'))
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findLiveBySlug(string $slug): ?Post
    {
        /** @var Post|null */
        return $this->liveQuery()
            ->where('slug', $slug)
            ->with([
                'featuredMedia', 'galleryMedia', 'categories:id,name,slug', 'tags:id,name,slug',
                'author:id,name', 'seoMeta.ogMedia', 'seoMeta.twitterMedia',
            ])
            ->first();
    }

    public function recent(int $limit): Collection
    {
        return $this->liveQuery()
            ->with(['featuredMedia', 'author:id,name'])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function popular(int $limit): Collection
    {
        return $this->liveQuery()
            ->with(['featuredMedia', 'author:id,name'])
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function archiveMonths(): BaseCollection
    {
        return $this->liveQuery()
            ->get(['published_at'])
            ->groupBy(fn (Post $post) => $post->published_at->format('Y-m'))
            ->map(fn ($group, $key) => [
                'label' => \Carbon\Carbon::createFromFormat('Y-m', $key)->format('F Y'),
                'count' => $group->count(),
            ])
            ->sortKeysDesc();
    }

    /**
     * Manual related first; fill remainder from shared categories.
     *
     * $manualOnly / $sameCategoryOnly / $sortOrder default to false/'latest',
     * matching the exact original behavior. Set via the Blog Detail page's
     * "Related Posts" CMS settings (Same Category Only / Manual Selection /
     * Sort Order).
     */
    public function relatedFor(Post $post, int $limit, bool $sameCategoryOnly = false, bool $manualOnly = false, string $sortOrder = 'latest'): Collection
    {
        $manual = $post->relatedPosts()
            ->live()
            ->with(['featuredMedia', 'author:id,name'])
            ->limit($limit)
            ->get();

        if ($manualOnly || $manual->count() >= $limit) {
            return $manual;
        }

        $categoryIds = $post->categories->pluck('id');

        $fallback = $this->liveQuery()
            ->with(['featuredMedia', 'author:id,name'])
            ->whereKeyNot($post->id)
            ->whereNotIn('id', $manual->pluck('id'))
            ->when($categoryIds->isNotEmpty(), function ($q) use ($categoryIds) {
                $q->whereHas('categories', fn ($c) => $c->whereIn('blog_categories.id', $categoryIds));
            })
            // Strict mode: never fall back to unrelated posts when the post
            // has no categories (default mode shows latest posts instead).
            ->when($sameCategoryOnly && $categoryIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
            ->when($sortOrder === 'oldest', fn ($q) => $q->orderBy('published_at'))
            ->when($sortOrder === 'popular', fn ($q) => $q->orderByDesc('views_count'))
            ->when($sortOrder !== 'oldest' && $sortOrder !== 'popular', fn ($q) => $q->orderByDesc('published_at'))
            ->limit($limit - $manual->count())
            ->get();

        return $manual->concat($fallback);
    }

    public function dueScheduled(): Collection
    {
        return $this->query()
            ->where('status', Post::STATUS_SCHEDULED)
            ->where('published_at', '<=', now())
            ->get();
    }

    public function optionsForRelated(?int $excludeId = null): Collection
    {
        return $this->query()
            ->when($excludeId, fn ($q) => $q->whereKeyNot($excludeId))
            ->orderByDesc('id')
            ->get(['id', 'title']);
    }

    private function liveQuery(): Builder
    {
        return $this->query()->live();
    }
}
