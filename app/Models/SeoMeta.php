<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_media_id',
        'schema_json',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_media_id',
        'breadcrumb_title',
        'sitemap_include',
        'sitemap_priority',
        'sitemap_changefreq',
    ];

    protected $casts = [
        'sitemap_include' => 'boolean',
    ];

    public function metable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    public function twitterMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'twitter_media_id');
    }
}
