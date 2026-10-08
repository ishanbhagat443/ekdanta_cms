<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Models\Page;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Repositories\Contracts\PageRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class PageRenderService
{
    public function __construct(
        protected PageRepositoryInterface $pages,
        protected MenuRepositoryInterface $menus,
    ) {
    }

    /**
     * Render payload for a published page: page + active sections with
     * resolved media and menus. Cached per slug until content changes.
     */
    public function payloadFor(string $slug): ?array
    {
        return Cache::rememberForever("page.render.{$slug}", function () use ($slug): ?array {
            $page = $this->pages->findPublishedBySlug($slug);

            return $page === null ? null : $this->livePayloadFor($page);
        });
    }

    /** Uncached payload — used for draft previews and inside payloadFor. */
    public function livePayloadFor(Page $page): array
    {
        $page->loadMissing([
            'sections' => fn ($q) => $q->where('is_active', true),
            'sections.developerOverride',
            'bannerMedia',
            'seoMeta.ogMedia',
            'seoMeta.twitterMedia',
        ]);

        $sections = $page->sections->map(fn ($section) => [
            'section' => $section,
            'menu' => ($menuId = $section->dataFor('desktop')['menu_id'] ?? null)
                ? $this->menus->tree((int) $menuId)
                : null,
            'media' => $this->collectMedia($section),
        ]);

        return ['page' => $page, 'sections' => $sections];
    }

    public function flushCache(?Page $page = null): void
    {
        if ($page !== null) {
            Cache::forget("page.render.{$page->slug}");

            return;
        }

        // Slug unknown (bulk change) — flush all page render entries.
        foreach (Page::withTrashed()->pluck('slug') as $slug) {
            Cache::forget("page.render.{$slug}");
        }
    }

    /**
     * Recursively harvest every media reference from all device payloads
     * plus the section background. Keys: media_id, *_media_id.
     *
     * @return array<int, Media>
     */
    private function collectMedia($section): array
    {
        $ids = [];

        $harvest = function ($value, ?string $key) use (&$harvest, &$ids): void {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $harvest($v, is_string($k) ? $k : null);
                }

                return;
            }

            if ($key !== null && str_ends_with($key, 'media_id') && is_numeric($value)) {
                $ids[] = (int) $value;
            }
        };

        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            $harvest($section->dataFor($device), null);
        }

        $harvest((array) $section->style, null);

        $ids = array_values(array_unique(array_filter($ids)));

        return $ids === []
            ? []
            : Media::query()->whereIn('id', $ids)->get()->keyBy('id')->all();
    }
}
