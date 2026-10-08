<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

interface PostRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateForAdmin(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator;

    /**
     * Public listing with sidebar filters. 'sort' and 'featured_first' are
     * optional (Blog Listing CMS grid settings) — omitting them preserves
     * the original behavior (orderByDesc('published_at') only).
     *
     * @param array{search?: ?string, category?: ?string, tag?: ?string, archive?: ?string, sort?: ?string, featured_first?: bool} $filters
     */
    public function paginateLive(array $filters, int $perPage): LengthAwarePaginator;

    public function findLiveBySlug(string $slug): ?Post;

    public function recent(int $limit): Collection;

    public function popular(int $limit): Collection;

    /** ["2026-07" => ["label" => "July 2026", "count" => 3], …] newest first. */
    public function archiveMonths(): BaseCollection;

    /**
     * Manual related first; fill remainder from shared categories.
     * $sameCategoryOnly / $manualOnly / $sortOrder default to false/'latest'
     * (original behavior).
     */
    public function relatedFor(Post $post, int $limit, bool $sameCategoryOnly = false, bool $manualOnly = false, string $sortOrder = 'latest'): Collection;

    /** Posts that become due: status=scheduled AND published_at <= now. */
    public function dueScheduled(): Collection;

    public function optionsForRelated(?int $excludeId = null): Collection;
}
