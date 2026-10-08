<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MenuItem extends Model
{
    /** Link type → [label, resolver model] used by the admin picker + resolvedUrl(). */
    public const LINK_TYPES = [
        'url' => 'External URL',
        'custom' => 'Custom Link',
        'page' => 'Page',
        'blog' => 'Blog Post',
        'blog_category' => 'Blog Category',
        'anchor' => 'Anchor Link',
        'phone' => 'Phone',
        'email' => 'Email',
    ];

    /** Link types resolved against a database row (reference_id + reference_type). */
    public const REFERENCE_TYPES = [
        'page' => Page::class,
        'blog' => Post::class,
        'blog_category' => BlogCategory::class,
    ];

    /** Content a mega-menu's direct child column can render. */
    public const COLUMN_TYPES = [
        'links' => 'Link List',
        'image' => 'Image Banner',
        'html' => 'Custom HTML',
        'cta' => 'CTA Button',
    ];

    public const BADGE_COLORS = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'nav_label',
        'link_type',
        'url',
        'reference_id',
        'reference_type',
        'target',
        'icon',
        'css_class',
        'element_id',
        'badge_text',
        'badge_color',
        'permission',
        'auth_only',
        'guest_only',
        'is_mega',
        'mega_columns',
        'column_type',
        'image_media_id',
        'custom_html',
        'cta_label',
        'cta_url',
        'cta_style',
        'featured_limit',
        'visible_devices',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_mega' => 'boolean',
        'is_active' => 'boolean',
        'auth_only' => 'boolean',
        'guest_only' => 'boolean',
        'sort_order' => 'integer',
        'mega_columns' => 'integer',
        'featured_limit' => 'integer',
        'visible_devices' => 'array',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    /** The linked Page/Post/BlogCategory row, if any. */
    public function reference(): ?Model
    {
        $class = self::REFERENCE_TYPES[$this->link_type] ?? null;

        if ($class === null || $this->reference_id === null) {
            return null;
        }

        return $class::find($this->reference_id);
    }

    /** Display label: nav_label wins when set, else the admin title. */
    public function displayLabel(): string
    {
        return $this->nav_label ?: $this->label;
    }

    /**
     * Resolved href for every link type. Falls back to '#' so a stale
     * reference (deleted page/post/etc.) never breaks the render.
     */
    public function resolvedUrl(): string
    {
        return match ($this->link_type) {
            'page' => $this->pageUrl(),
            'blog' => $this->blogUrl(),
            'blog_category' => $this->blogCategoryUrl(),
            'anchor' => $this->url ? '#'.ltrim($this->url, '#') : '#',
            'phone' => $this->url ? 'tel:'.preg_replace('/\s+/', '', $this->url) : '#',
            'email' => $this->url ? 'mailto:'.$this->url : '#',
            'url', 'custom' => $this->url ?: '#',
            default => $this->url ?: '#',
        };
    }

    private function pageUrl(): string
    {
        /** @var Page|null $page */
        $page = $this->reference();

        return $page ? ($page->slug === 'home' ? url('/') : url('/'.$page->slug)) : '#';
    }

    private function blogUrl(): string
    {
        /** @var Post|null $post */
        $post = $this->reference();

        return $post ? url('/blogs/'.$post->slug) : '#';
    }

    private function blogCategoryUrl(): string
    {
        /** @var BlogCategory|null $category */
        $category = $this->reference();

        return $category ? url('/blogs?category='.$category->slug) : '#';
    }

    /** Visible to the current visitor: permission gate + auth/guest gate. */
    public function isVisibleToCurrentUser(): bool
    {
        $user = Auth::user();

        if ($this->auth_only && ! $user) {
            return false;
        }

        if ($this->guest_only && $user) {
            return false;
        }

        if ($this->permission && (! $user || ! $user->can($this->permission))) {
            return false;
        }

        return true;
    }

    /** CSS visibility classes for the configured devices (empty = all devices). */
    public function deviceVisibilityClasses(): string
    {
        $devices = $this->visible_devices;

        if (empty($devices) || count($devices) === 3) {
            return '';
        }

        $classes = [];
        $classes[] = in_array('mobile', $devices, true) ? 'd-block' : 'd-none';
        $classes[] = in_array('tablet', $devices, true) ? 'd-md-block' : 'd-md-none';
        $classes[] = in_array('desktop', $devices, true) ? 'd-lg-block' : 'd-lg-none';

        return implode(' ', $classes);
    }

    public function badgeClass(): string
    {
        $color = in_array($this->badge_color, self::BADGE_COLORS, true) ? $this->badge_color : 'primary';

        return "badge badge-{$color}";
    }


    /** True when this item is currently active/current-page for nav highlighting. */
    public function isCurrent(): bool
    {
        $path = $this->resolvedUrl();

        if (! Str::startsWith($path, [url('/'), '/'])) {
            return false;
        }

        return request()->is(ltrim(str_replace(url('/'), '', $path), '/') ?: '/');
    }
}
