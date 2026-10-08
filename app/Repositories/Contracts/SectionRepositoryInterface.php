<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface SectionRepositoryInterface
{
    /** @return Collection<int, Section> ordered, active + inactive */
    public function forOwner(Model $owner): Collection;

    public function findSection(int $id): Section;

    public function createFor(Model $owner, array $attributes): Section;

    public function updateSection(Section $section, array $attributes): Section;

    public function deleteSection(Section $section): void;

    /** @param list<int> $orderedIds */
    public function reorder(Model $owner, array $orderedIds): void;

    public function nextSortOrder(Model $owner): int;
}
