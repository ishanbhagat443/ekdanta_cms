<?php

declare(strict_types=1);

namespace App\Services\SectionTypes;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class SectionTypeRegistry
{
    /** @var array<string, SectionType> */
    private array $types = [];

    public function register(SectionType $type): void
    {
        $this->types[$type->key()] = $type;
    }

    public function find(string $key): SectionType
    {
        if (! isset($this->types[$key])) {
            throw new InvalidArgumentException("Unknown section type [{$key}].");
        }

        return $this->types[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->types[$key]);
    }

    /** @return Collection<int, SectionType> */
    public function forArea(string $area): Collection
    {
        return collect($this->types)
            ->filter(fn (SectionType $t) => in_array($area, $t->allowedAreas(), true))
            ->values();
    }

    /** @return Collection<int, SectionType> */
    public function all(): Collection
    {
        return collect($this->types)->values();
    }
}
