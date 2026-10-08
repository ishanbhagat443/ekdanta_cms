<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Section;
use App\Repositories\Contracts\SectionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SectionRepository implements SectionRepositoryInterface
{
    public function forOwner(Model $owner): Collection
    {
        return $owner->sections()->orderBy('sort_order')->get();
    }

    public function findSection(int $id): Section
    {
        return Section::query()->findOrFail($id);
    }

    public function createFor(Model $owner, array $attributes): Section
    {
        return $owner->sections()->create($attributes);
    }

    public function updateSection(Section $section, array $attributes): Section
    {
        $section->update($attributes);

        return $section->refresh();
    }

    public function deleteSection(Section $section): void
    {
        $section->delete();
    }

    public function reorder(Model $owner, array $orderedIds): void
    {
        foreach (array_values($orderedIds) as $position => $id) {
            $owner->sections()->where('id', $id)->update(['sort_order' => $position]);
        }
    }

    public function nextSortOrder(Model $owner): int
    {
        return (int) $owner->sections()->max('sort_order') + 1;
    }
}
