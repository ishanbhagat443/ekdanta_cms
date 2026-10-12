<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasSeoMeta;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'show_banner',
        'banner_title',
        'banner_media_id',
        'settings',
        'published_at',
    ];

    protected $casts = [
        'show_banner' => 'boolean',
        'settings' => 'array',
        'published_at' => 'datetime',
    ];

    /** Page settings → Conversion tags: IDs only (the tags themselves are fixed), so no Developer Mode needed. */
    public const CONVERSION_RULES = [
        'meta_pixel' => ['nullable', 'regex:/^\d{5,20}$/'],
        'ads_id' => ['nullable', 'regex:/^AW-\d{5,15}$/'],
        'ads_label' => ['nullable', 'regex:/^[A-Za-z0-9_-]{1,40}$/'],
    ];

    public function sections(): MorphMany
    {
        return $this->morphMany(Section::class, 'sectionable')->orderBy('sort_order');
    }

    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function effectiveBannerTitle(): string
    {
        return $this->banner_title ?: $this->title;
    }

    /**
     * Where an admin previews this page, drafts included. The home page is
     * served at "/", never "/home", so its preview lives there too.
     */
    public function previewUrl(): string
    {
        return url($this->isHomePage() ? '/' : '/'.$this->slug).'?preview=1';
    }

    /** The page marked as home (Page settings → role), else the "home" slug. */
    public function isHomePage(): bool
    {
        $role = (int) setting('pages.home_id', 0);

        return $role !== 0 ? $role === $this->id : $this->slug === 'home';
    }

    /** Read one value from the page's JSON settings store (dot-free key). */
    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
