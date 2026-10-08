<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use App\Repositories\Contracts\PageRepositoryInterface;
use Illuminate\Support\Str;

class PageService
{
    public function __construct(
        protected PageRepositoryInterface $pages,
        protected PageRenderService $renderer,
    ) {
    }

    public function createPage(array $data): Page
    {
        /** @var Page $page */
        $page = $this->pages->create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['title']),
            'status' => $data['status'] ?? Page::STATUS_DRAFT,
            'show_banner' => (bool) ($data['show_banner'] ?? true),
            'banner_title' => $data['banner_title'] ?? null,
            'banner_media_id' => $data['banner_media_id'] ?? null,
            'published_at' => ($data['status'] ?? null) === Page::STATUS_PUBLISHED ? now() : null,
        ]);

        // A page created straight into "published" belongs in the sitemap now,
        // not whenever the hour-long cache happens to expire.
        \Illuminate\Support\Facades\Cache::forget('seo.sitemap');

        return $page;
    }

    public function updatePage(Page $page, array $data): Page
    {
        $oldSlug = $page->slug;

        $attributes = [
            'title' => $data['title'],
            'status' => $data['status'] ?? $page->status,
            'show_banner' => (bool) ($data['show_banner'] ?? false),
            'banner_title' => $data['banner_title'] ?? null,
            'banner_media_id' => $data['banner_media_id'] ?? null,
        ];

        // Per-page JSON settings (e.g. the Collection page's filter config).
        // Only overwritten when the form actually submitted settings, so other
        // pages' saves never clear it.
        if (array_key_exists('settings', $data)) {
            $attributes['settings'] = $data['settings'];
        }

        if (! empty($data['slug']) && $data['slug'] !== $page->slug) {
            $attributes['slug'] = $this->uniqueSlug($data['slug'], $page->id);
        }

        if ($attributes['status'] === Page::STATUS_PUBLISHED && $page->published_at === null) {
            $attributes['published_at'] = now();
        }

        $page = $this->pages->update($page, $attributes);

        // Flush both old and new slug cache entries.
        \Illuminate\Support\Facades\Cache::forget("page.render.{$oldSlug}");
        $this->renderer->flushCache($page);
        \Illuminate\Support\Facades\Cache::forget('seo.sitemap');

        return $page;
    }

    public function deletePage(Page $page): void
    {
        $this->renderer->flushCache($page);
        \Illuminate\Support\Facades\Cache::forget('seo.sitemap');
        $this->pages->delete($page); // soft delete; sections stay attached
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'page';
        $slug = $base;
        $suffix = 2;

        while (Page::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
