<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ThemeRepositoryInterface
{
    /** All theme_settings rows, unscoped. */
    public function allSettings(): Collection;

    /** Rows for one group. */
    public function byGroup(string $group): Collection;

    /** Insert-or-update a single value. */
    public function upsertValue(string $group, string $key, ?string $value, string $type = 'text'): void;

    /** Replace every row with a full [ "group.key" => value ] map (used by Import/Reset). */
    public function replaceAll(array $flatMap): void;
}
