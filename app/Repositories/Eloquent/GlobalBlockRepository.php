<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\GlobalBlock;
use App\Repositories\Contracts\GlobalBlockRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GlobalBlockRepository implements GlobalBlockRepositoryInterface
{
    public function paginateForAdmin(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? 'newest';

        return GlobalBlock::query()
            ->withCount('instances')
            ->with(['category', 'previewMedia', 'author'])
            ->when($filters['search'] ?? null, function ($q, string $term): void {
                $q->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas(
                'category', fn ($c) => $c->where('slug', $slug)
            ))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when(! empty($filters['favorites']), fn ($q) => $q->where('is_favorite', true))
            ->when($sort === 'recent', fn ($q) => $q->orderByDesc('last_used_at'))
            ->when($sort === 'most_used', fn ($q) => $q->orderByDesc('use_count'))
            ->when($sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($sort === 'newest', fn ($q) => $q->latest('id'))
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findBySlug(string $slug): ?GlobalBlock
    {
        return GlobalBlock::query()->where('slug', $slug)->first();
    }

    public function activeOptions(): Collection
    {
        return GlobalBlock::query()
            ->active()
            ->with(['category', 'previewMedia'])
            ->orderBy('name')
            ->get();
    }

    public function create(array $attributes): GlobalBlock
    {
        return GlobalBlock::create($attributes);
    }

    public function update(GlobalBlock $block, array $attributes): GlobalBlock
    {
        $block->update($attributes);

        return $block->refresh();
    }

    public function delete(GlobalBlock $block): void
    {
        $block->delete();
    }
}
