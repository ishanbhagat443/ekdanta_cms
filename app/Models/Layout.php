<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Layout extends Model
{
    public const AREA_HEADER = 'header';

    public const AREA_TOPBAR = 'topbar';

    public const AREA_FOOTER = 'footer';

    protected $fillable = [
        'area',
        'settings',
        'custom_css',
        'custom_js',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function sections(): MorphMany
    {
        return $this->morphMany(Section::class, 'sectionable')->orderBy('sort_order');
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /** Header style: normal | overlay | pill | hidden (a page may override it). */
    public function headerVariant(): string
    {
        return $this->setting('variant') ?: ($this->setting('overlay_hero') ? 'overlay' : 'normal');
    }

    /** Header navigation style on a device: inline | dropdown | overlay. */
    public function navStyle(string $device): string
    {
        return $this->setting("nav_style.{$device}") ?: ($device === 'desktop' ? 'inline' : 'dropdown');
    }
}
