<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Facades\Cache;

/**
 * Editable blocks for bespoke screens rendered by their own blade rather
 * than the drag-and-drop Page Builder.
 *
 * Definitions live in config/page_templates.php; saved values live in the
 * page's `settings.blocks` JSON. Because each screen is driven by ONE page
 * row, editing a block changes every screen of that kind.
 */
class PageTemplateService
{
    private const CACHE_KEY = 'page_templates.blocks';

    /** @var array<string, array<string, array<string, mixed>>> per-request memo */
    private array $memo = [];

    /** Template definition for a slug, or null when the page is a normal one. */
    public function definition(string $slug): ?array
    {
        return config("page_templates.{$slug}");
    }

    /** Every slug that is a template page. */
    public function slugs(): array
    {
        return array_keys((array) config('page_templates', []));
    }

    /**
     * Resolved blocks for a slug: config defaults merged with whatever the
     * admin saved. Always returns every declared block and field.
     *
     * @return array<string, array<string, mixed>>
     */
    public function blocks(string $slug): array
    {
        if (isset($this->memo[$slug])) {
            return $this->memo[$slug];
        }

        $all = Cache::rememberForever(self::CACHE_KEY, function (): array {
            $stored = Page::query()
                ->whereIn('slug', array_keys((array) config('page_templates', [])))
                ->pluck('settings', 'slug')
                ->map(fn ($settings) => (array) data_get($settings, 'blocks', []))
                ->all();

            $resolved = [];

            foreach ((array) config('page_templates', []) as $pageSlug => $definition) {
                foreach ((array) ($definition['blocks'] ?? []) as $blockKey => $block) {
                    foreach ((array) ($block['fields'] ?? []) as $fieldKey => $field) {
                        $saved = data_get($stored, "{$pageSlug}.{$blockKey}.{$fieldKey}");

                        $resolved[$pageSlug][$blockKey][$fieldKey] = $saved !== null
                            ? $this->cast($saved, $field['type'] ?? 'text')
                            : ($field['default'] ?? null);
                    }
                }
            }

            return $resolved;
        });

        return $this->memo[$slug] = (array) ($all[$slug] ?? []);
    }

    /** A single field, falling back to the config default then $default. */
    public function get(string $slug, string $block, string $field, mixed $default = null): mixed
    {
        $value = data_get($this->blocks($slug), "{$block}.{$field}");

        return $value ?? $default;
    }

    /** Convenience: is this block switched on? */
    public function enabled(string $slug, string $block): bool
    {
        return (bool) $this->get($slug, $block, 'enabled', true);
    }

    /**
     * Normalise a submitted block payload against the declared fields, so only
     * known keys with the right types are persisted.
     */
    public function sanitise(string $slug, array $submitted): array
    {
        $clean = [];

        foreach ((array) config("page_templates.{$slug}.blocks", []) as $blockKey => $block) {
            foreach ((array) ($block['fields'] ?? []) as $fieldKey => $field) {
                $type = $field['type'] ?? 'text';
                $raw = data_get($submitted, "{$blockKey}.{$fieldKey}");

                $clean[$blockKey][$fieldKey] = $type === 'toggle'
                    ? (bool) $raw
                    : ($raw === null || $raw === '' ? ($field['default'] ?? null) : $this->cast($raw, $type));
            }
        }

        return $clean;
    }

    public function flushCache(): void
    {
        $this->memo = [];
        Cache::forget(self::CACHE_KEY);
    }

    private function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'toggle' => (bool) $value,
            'number' => (int) $value,
            default => (string) $value,
        };
    }
}
