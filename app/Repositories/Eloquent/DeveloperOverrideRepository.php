<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\DeveloperOverride;
use App\Models\Section;
use App\Repositories\Contracts\DeveloperOverrideRepositoryInterface;

class DeveloperOverrideRepository implements DeveloperOverrideRepositoryInterface
{
    public function findForSection(Section $section): ?DeveloperOverride
    {
        return DeveloperOverride::query()->where('section_id', $section->id)->first();
    }

    public function upsertForSection(Section $section, array $data): DeveloperOverride
    {
        return DeveloperOverride::query()->updateOrCreate(
            ['section_id' => $section->id],
            $data,
        );
    }

    public function delete(DeveloperOverride $override): void
    {
        $override->delete();
    }
}
