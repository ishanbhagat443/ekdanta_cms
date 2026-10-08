<?php

declare(strict_types=1);

namespace App\Services\Concerns;

use Illuminate\Support\Str;

trait GeneratesUniqueSlug
{
    /**
     * Slug from explicit input or fallback source, made unique within
     * the given model's table.
     *
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    protected function uniqueSlugFor(string $modelClass, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'item';
        $slug = $base;
        $suffix = 2;

        $query = fn (string $candidate) => $modelClass::query()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();

        while ($query($slug)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
