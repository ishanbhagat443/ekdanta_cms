<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Section extends Model
{
    protected $fillable = [
        'type',
        'name',
        'sort_order',
        'is_active',
        'data_desktop',
        'data_tablet',
        'data_mobile',
        'style',
        'global_block_id',
        'is_synced',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'data_desktop' => 'array',
        'data_tablet' => 'array',
        'data_mobile' => 'array',
        'style' => 'array',
        'is_synced' => 'boolean',
    ];

    public function sectionable(): MorphTo
    {
        return $this->morphTo();
    }

    /** The reusable-block master this instance was placed from, if any. */
    public function globalBlock(): BelongsTo
    {
        return $this->belongsTo(GlobalBlock::class);
    }

    /** Developer Mode HTML/CSS/JS override for this section, if any. */
    public function developerOverride(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DeveloperOverride::class);
    }

    /**
     * Active override or null. Uses the eager-loaded relation when present
     * (frontend render paths) and an explicit query otherwise — never a
     * lazy-load, so preventLazyLoading() stays happy.
     */
    public function activeDeveloperOverride(): ?DeveloperOverride
    {
        $override = $this->relationLoaded('developerOverride')
            ? $this->getRelation('developerOverride')
            : $this->developerOverride()->first();

        return $override && $override->is_active ? $override : null;
    }

    public function isLinkedToGlobalBlock(): bool
    {
        return $this->global_block_id !== null && $this->is_synced;
    }

    /**
     * Effective payload for a device. Tablet/mobile store only overridden
     * keys; anything missing inherits from desktop (empty string = inherit).
     */
    public function dataFor(string $device): array
    {
        $desktop = (array) ($this->data_desktop ?? []);

        if ($device === 'desktop') {
            return $desktop;
        }

        $overrides = (array) ($this->{'data_'.$device} ?? []);
        $overrides = array_filter($overrides, fn ($v) => $v !== null && $v !== '');

        return array_replace($desktop, $overrides);
    }

    /**
     * Effective value of one key per device, collapsed: returns
     * ['*' => value] when all devices agree, otherwise per-device map.
     * Blades use this to emit breakpoint-scoped variants (SEO-safe,
     * server-rendered — no JS swapping).
     */
    public function deviceValues(string $key): array
    {
        $values = [
            'desktop' => $this->dataFor('desktop')[$key] ?? null,
            'tablet' => $this->dataFor('tablet')[$key] ?? null,
            'mobile' => $this->dataFor('mobile')[$key] ?? null,
        ];

        if (count(array_unique(array_map('strval', $values))) === 1) {
            return ['*' => $values['desktop']];
        }

        return $values;
    }

    /** Per-device style value, e.g. styleFor('visible.mobile', true). */
    public function styleValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->style, $key, $default);
    }

    /**
     * Bootstrap column classes for footer-style grid widgets.
     * style.width = {desktop: 1-12, tablet: 1-12, mobile: 1-12}.
     */
    public function widthClasses(): string
    {
        $mobile = (int) $this->styleValue('width.mobile', 12);
        $tablet = (int) $this->styleValue('width.tablet', 6);
        $desktop = (int) $this->styleValue('width.desktop', 3);

        return "col-{$mobile} col-md-{$tablet} col-lg-{$desktop}";
    }

    /** Bootstrap display classes implementing per-device visibility. */
    public function visibilityClasses(): string
    {
        $classes = [];

        $mobile = (bool) $this->styleValue('visible.mobile', true);
        $tablet = (bool) $this->styleValue('visible.tablet', true);
        $desktop = (bool) $this->styleValue('visible.desktop', true);

        if ($mobile && $tablet && $desktop) {
            return '';
        }

        $classes[] = $mobile ? 'd-block' : 'd-none';
        $classes[] = $tablet ? 'd-md-block' : 'd-md-none';
        $classes[] = $desktop ? 'd-lg-block' : 'd-lg-none';

        return implode(' ', $classes);
    }
}
