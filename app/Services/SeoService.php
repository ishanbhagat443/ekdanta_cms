<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
use App\Models\SeoMeta;
use Illuminate\Support\Facades\Cache;

class SeoService
{
    /**
     * Resolve the head payload for an entity: per-entity seo_meta first,
     * then the caller's contextual defaults, then site-wide SEO settings.
     * Output feeds frontend.partials.seo-head.
     *
     * @param array{title?: ?string, description?: ?string, canonical?: ?string, og_type?: string} $context
     */
    public function resolve(?SeoMeta $meta, array $context = []): array
    {
        $contextTitle = $context['title'] ?? null;
        $defaultTitle = setting('seo.meta_title');

        $title = $meta?->meta_title
            ?: ($defaultTitle && $contextTitle
                ? str_replace('{page}', $contextTitle, $defaultTitle)
                : ($contextTitle ?: $defaultTitle));

        $description = $meta?->meta_description ?: ($context['description'] ?? setting('seo.meta_description'));
        $ogImage = $meta?->ogMedia?->url ?? setting_image('seo.og_image');

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $meta?->meta_keywords ?: setting('seo.meta_keywords'),
            'canonical' => $meta?->canonical_url ?: ($context['canonical'] ?? $this->canonicalFor(request()->path())),
            'robots' => $meta?->robots,
            'og_type' => $context['og_type'] ?? 'website',
            'og_title' => $meta?->og_title ?: (setting('seo.og_title') && ! $meta?->meta_title ? setting('seo.og_title') : null),
            'og_description' => $meta?->og_description ?: setting('seo.og_description'),
            'og_image' => $ogImage,
            'twitter_card' => $meta?->twitter_card,
            'twitter_title' => $meta?->twitter_title,
            'twitter_description' => $meta?->twitter_description,
            'twitter_image' => $meta?->twitterMedia?->url,
            'schema' => $meta?->schema_json,
        ];
    }

    /** Canonical URL from the configured base (falls back to app URL). */
    public function canonicalFor(string $path): string
    {
        $base = rtrim(setting('seo.canonical') ?: config('app.url'), '/');
        $path = trim($path, '/');

        return $path === '' || $path === 'home' ? $base : "{$base}/{$path}";
    }

    /**
     * BreadcrumbList JSON-LD.
     *
     * @param list<array{name: string, url?: string}> $crumbs (Home is prepended)
     */
    public function breadcrumbSchema(array $crumbs): array
    {
        $items = [['name' => 'Home', 'url' => $this->canonicalFor('/')], ...$crumbs];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($crumb, $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'] ?? null,
            ]))->all(),
        ];
    }

    /* ── XML sitemap ───────────────────────────────────────────── */

    public function sitemapXml(): string
    {
        return Cache::remember('seo.sitemap', now()->addHour(), function (): string {
            $urls = '';

            foreach ($this->sitemapEntries() as $entry) {
                $urls .= "  <url>\n    <loc>".e($entry['loc'])."</loc>\n";
                if (! empty($entry['lastmod'])) {
                    $urls .= '    <lastmod>'.$entry['lastmod']->toAtomString()."</lastmod>\n";
                }
                if (! empty($entry['changefreq'])) {
                    $urls .= '    <changefreq>'.e($entry['changefreq'])."</changefreq>\n";
                }
                if (! empty($entry['priority'])) {
                    $urls .= '    <priority>'.e($entry['priority'])."</priority>\n";
                }
                $urls .= "  </url>\n";
            }

            return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
                ."<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n{$urls}</urlset>\n";
        });
    }

    public function flushSitemap(): void
    {
        Cache::forget('seo.sitemap');
    }

    /** @return list<array{loc: string, lastmod: ?\Illuminate\Support\Carbon, changefreq: ?string, priority: ?string}> */
    private function sitemapEntries(): array
    {
        $entries = [];

        $pages = Page::query()
            ->where('status', Page::STATUS_PUBLISHED)
            ->with('seoMeta')
            ->orderBy('id')
            ->get();

        foreach ($pages as $page) {
            if ($this->excludedFromSitemap($page->seoMeta)) {
                continue;
            }

            $isHome = $page->slug === 'home';
            $entries[] = [
                'loc' => $this->canonicalFor($isHome ? '/' : $page->slug),
                'lastmod' => $page->updated_at,
                'changefreq' => $page->seoMeta?->sitemap_changefreq ?: ($isHome ? 'weekly' : 'monthly'),
                'priority' => $page->seoMeta?->sitemap_priority ?: ($isHome ? '1.0' : '0.8'),
            ];
        }

        $posts = Post::query()
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('published_at', '<=', now())
            ->with('seoMeta')
            ->orderBy('id')
            ->get();

        if ($posts->isNotEmpty()) {
            $entries[] = [
                'loc' => $this->canonicalFor('blogs'),
                'lastmod' => $posts->max('updated_at'),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        foreach ($posts as $post) {
            if ($this->excludedFromSitemap($post->seoMeta)) {
                continue;
            }

            $entries[] = [
                'loc' => $this->canonicalFor("blogs/{$post->slug}"),
                'lastmod' => $post->updated_at,
                'changefreq' => $post->seoMeta?->sitemap_changefreq ?: 'monthly',
                'priority' => $post->seoMeta?->sitemap_priority ?: '0.6',
            ];
        }

        return $entries;
    }

    private function excludedFromSitemap(?SeoMeta $meta): bool
    {
        if ($meta === null) {
            return false;
        }

        return ! $meta->sitemap_include
            || str_contains((string) $meta->robots, 'noindex');
    }

    /* ── robots.txt ────────────────────────────────────────────── */

    public function robotsTxt(): string
    {
        $content = trim((string) setting('seo.robots'))
            ?: "User-agent: *\nDisallow: /cms\nAllow: /";

        // The CMS value may already carry its own Sitemap directive — appending
        // a second one would emit a duplicate. Only add ours when absent.
        if (! preg_match('/^\s*sitemap\s*:/mi', $content)) {
            $content .= "\n\nSitemap: ".$this->canonicalFor('sitemap.xml');
        }

        return $content."\n";
    }
}
