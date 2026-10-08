<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\DeveloperOverride;
use App\Models\Section;

interface DeveloperOverrideRepositoryInterface
{
    public function findForSection(Section $section): ?DeveloperOverride;

    /** Create or update the section's override. */
    public function upsertForSection(Section $section, array $data): DeveloperOverride;

    public function delete(DeveloperOverride $override): void;
}
