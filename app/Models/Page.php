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

    /** Read one value from the page's JSON settings store (dot-free key). */
    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
