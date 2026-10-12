<?php

declare(strict_types=1);

namespace App\Collections;

use Illuminate\Support\Collection;

/** Every collection the CMS keeps. New ones: a class here, nothing else. */
class CollectionRegistry
{
    private const TYPES = [
        CaseStudies::class,
        ServiceGroups::class,
        Services::class,
        Clients::class,
        Reels::class,
        Faqs::class,
        TeamRoles::class,
    ];

    /** @var array<string, CollectionType>|null */
    private static ?array $types = null;

    /** @return Collection<string, CollectionType> */
    public function all(): Collection
    {
        self::$types ??= collect(self::TYPES)->map(fn ($class) => new $class())->keyBy(fn (CollectionType $t) => $t->key())->all();

        return collect(self::$types);
    }

    public function find(string $key): CollectionType
    {
        return $this->all()->get($key) ?? abort(404);
    }
}
