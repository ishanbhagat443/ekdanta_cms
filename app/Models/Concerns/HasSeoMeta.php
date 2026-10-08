<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeoMeta
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'metable');
    }

    /**
     * Create/update/prune the SEO row from validated form input
     * (keys prefixed "seo_"). All fields empty → row removed.
     */
    public function syncSeoMeta(array $input): void
    {
        $fields = [
            'meta_title' => $input['seo_meta_title'] ?? null,
            'meta_description' => $input['seo_meta_description'] ?? null,
            'meta_keywords' => $input['seo_meta_keywords'] ?? null,
            'canonical_url' => $input['seo_canonical_url'] ?? null,
            'robots' => $input['seo_robots'] ?? null,
            'og_title' => $input['seo_og_title'] ?? null,
            'og_description' => $input['seo_og_description'] ?? null,
            'og_media_id' => $input['seo_og_media_id'] ?? null,
            'schema_json' => $input['seo_schema_json'] ?? null,
            'twitter_card' => $input['seo_twitter_card'] ?? null,
            'twitter_title' => $input['seo_twitter_title'] ?? null,
            'twitter_description' => $input['seo_twitter_description'] ?? null,
            'twitter_media_id' => $input['seo_twitter_media_id'] ?? null,
            'breadcrumb_title' => $input['seo_breadcrumb_title'] ?? null,
            'sitemap_priority' => $input['seo_sitemap_priority'] ?? null,
            'sitemap_changefreq' => $input['seo_sitemap_changefreq'] ?? null,
        ];

        $sitemapInclude = ! array_key_exists('seo_sitemap_include', $input)
            || (bool) $input['seo_sitemap_include'];

        // Row is only worth keeping when something differs from the defaults.
        if ($sitemapInclude && array_filter($fields, fn ($v) => $v !== null && $v !== '') === []) {
            $this->seoMeta()->delete();

            return;
        }

        $this->seoMeta()->updateOrCreate([], $fields + ['sitemap_include' => $sitemapInclude]);
    }

    /** Shared validation rules for the SEO fieldset partial. */
    public static function seoRules(): array
    {
        return [
            'seo_meta_title' => ['nullable', 'string', 'max:191'],
            'seo_meta_description' => ['nullable', 'string', 'max:500'],
            'seo_meta_keywords' => ['nullable', 'string', 'max:500'],
            'seo_canonical_url' => ['nullable', 'url', 'max:191'],
            'seo_robots' => ['nullable', 'string', 'max:50'],
            'seo_og_title' => ['nullable', 'string', 'max:191'],
            'seo_og_description' => ['nullable', 'string', 'max:500'],
            'seo_og_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'seo_schema_json' => ['nullable', 'string', 'max:10000', 'json'],
            'seo_twitter_card' => ['nullable', 'in:summary,summary_large_image'],
            'seo_twitter_title' => ['nullable', 'string', 'max:191'],
            'seo_twitter_description' => ['nullable', 'string', 'max:500'],
            'seo_twitter_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'seo_breadcrumb_title' => ['nullable', 'string', 'max:191'],
            'seo_sitemap_include' => ['nullable', 'boolean'],
            'seo_sitemap_priority' => ['nullable', 'in:0.1,0.2,0.3,0.4,0.5,0.6,0.7,0.8,0.9,1.0'],
            'seo_sitemap_changefreq' => ['nullable', 'in:always,hourly,daily,weekly,monthly,yearly,never'],
        ];
    }
}
