<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Media;
use App\Repositories\Contracts\MediaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MediaRepository extends BaseRepository implements MediaRepositoryInterface
{
    public function __construct(Media $model)
    {
        parent::__construct($model);
    }

    public function browse(
        ?int $folderId,
        ?string $search,
        ?string $type,
        bool $anyFolder,
        int $perPage
    ): LengthAwarePaginator {
        return $this->query()
            ->when(! $anyFolder, fn ($q) => $q->where('folder_id', $folderId))
            ->when($search, function ($q, string $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('filename', 'like', "%{$term}%")
                        ->orWhere('title', 'like', "%{$term}%")
                        ->orWhere('alt', 'like', "%{$term}%");
                });
            })
            ->when($type, fn ($q, string $t) => $q->where('type', $t))
            ->latest('id')
            ->paginate($perPage);
    }

    public function byIdsOrdered(array $ids): Collection
    {
        if ($ids === []) {
            return new Collection();
        }

        return $this->query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Media $m) => array_search($m->id, $ids, true))
            ->values();
    }
}
