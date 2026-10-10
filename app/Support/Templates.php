<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\CollectionItem;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Collection;

/**
 * A page marked "Template for …" (page settings) draws every item of a
 * kind at its own address: case studies at /work/{slug}, services with a
 * page at /services/{slug}, blog articles at /blog/{slug}. Its sections read
 * the item as $templateItem.
 */
final class Templates
{
    public const TYPES = [
        'case_study' => 'Case study (/work/…)',
        'service' => 'Service (/services/…)',
        'post' => 'Blog article (/blog/…)',
    ];

    private const COLLECTIONS = ['case_study' => 'case-studies', 'service' => 'services'];

    /** The page marked as this template (the first one, if several are). */
    public static function page(string $template, bool $publishedOnly = true): ?Page
    {
        return Page::query()
            ->where('settings->template_for', $template)
            ->when($publishedOnly, fn ($q) => $q->where('status', Page::STATUS_PUBLISHED))
            ->orderBy('id')
            ->first();
    }

    /** One item by slug, or the first one when $slug is null. Drafts only when asked (previews). */
    public static function item(string $template, ?string $slug, bool $drafts = false): CollectionItem|Post|null
    {
        if ($template === 'post') {
            $query = $drafts ? Post::query()->latest('id') : Post::query()->live()->latest('published_at');
        } else {
            $query = CollectionItem::query()->of(self::COLLECTIONS[$template])
                ->when(! $drafts, fn ($q) => $q->published())
                ->when($template === 'service', fn ($q) => $q->where('data->has_page', true));
        }

        return $slug === null ? $query->first() : $query->where('slug', $slug)->first();
    }

    /** slug => title, for the builder preview's item picker. */
    public static function options(string $template): Collection
    {
        return match ($template) {
            'post' => Post::query()->latest('id')->limit(200)->pluck('title', 'slug'),
            'service' => CollectionItem::query()->of('services')->where('data->has_page', true)->pluck('title', 'slug'),
            default => CollectionItem::query()->of(self::COLLECTIONS[$template] ?? '')->pluck('title', 'slug'),
        };
    }
}
