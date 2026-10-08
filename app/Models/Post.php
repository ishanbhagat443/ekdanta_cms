<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasSeoMeta;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_SCHEDULED = 'scheduled';

    protected $fillable = [
        'title',
        'slug',
        'user_id',
        'author_name',
        'excerpt',
        'body',
        'featured_media_id',
        'status',
        'is_featured',
        'published_at',
        'views_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    /* ── Relations ─────────────────────────────────────────────── */

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function galleryMedia(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable')
            ->wherePivot('collection', 'gallery')
            ->withPivot(['collection', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function relatedPosts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'related_posts', 'post_id', 'related_post_id')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /* ── Scopes / helpers ──────────────────────────────────────── */

    /** Publicly visible: published AND publish date reached. */
    public function scopeLive(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->where('published_at', '<=', now());
    }

    public function isLive(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function displayAuthor(): string
    {
        if ($this->author_name) {
            return $this->author_name;
        }

        // Never lazy-load in views (preventLazyLoading is enabled).
        $authorName = $this->relationLoaded('author') ? $this->author?->name : null;

        return $authorName ?? (string) setting('business.company_name', '');
    }

    public function autoExcerpt(int $words = 22): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        return Str::words(trim(strip_tags((string) $this->body)), $words, '…');
    }

    /** Estimated reading time in minutes (~200 words/min), computed — not stored. */
    public function readingTimeMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->body));

        return max(1, (int) ceil($words / 200));
    }
}
