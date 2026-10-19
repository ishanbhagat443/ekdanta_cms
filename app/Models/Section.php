<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Section extends Model
{
    use Concerns\HasDeviceData;

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
    /** The values a view draws: dataFor() with {phone:1}, {whatsapp}, {email} filled from Settings → Business. */
    public function viewData(string $device): array
    {
        return business_tokens($this->dataFor($device));
    }

    /**
     * The compositions an Ekdanta view draws: desktop and phone, plus a
     * tablet one only when the tablet's values differ from the phone's
     * (otherwise tablets show the phone composition).
     *
     * @return array<string, array<string, mixed>>
     */
    public function comps(array $desktop, array $mobile): array
    {
        $same = $this->dataFor('tablet') === $this->dataFor('mobile');

        return ['desktop' => $desktop, 'mobile' => $mobile] + ($same ? [] : ['tablet' => $this->viewData('tablet')]);
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

    /**
     * The gate every style save and import passes. Section CSS/JS reach every
     * visitor, so writing them is the same privilege as Developer Mode.
     * Dropped rather than nulled: saves merge style, so leaving the keys out
     * keeps whatever a developer wrote. Motion is data for everyone, cleaned.
     */
    public static function withoutPrivilegedStyle(array $style): array
    {
        if (isset($style['motion'])) {
            $style['motion'] = \App\Support\SectionMotion::sanitiseStored((array) $style['motion']);
        }

        if (\Illuminate\Support\Facades\Gate::allows('developer.mode')) {
            return $style;
        }

        unset($style['custom_css'], $style['custom_js']);

        return $style;
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
        return self::displayClasses(
            (bool) $this->styleValue('visible.mobile', true),
            (bool) $this->styleValue('visible.tablet', true),
            (bool) $this->styleValue('visible.desktop', true),
        );
    }

    /**
     * Bootstrap classes that show an element only on the given devices
     * (mobile < 768 px, tablet < 992 px, desktop above), plus gsm-t-* for
     * tablets 992 px and wider held upright (frontend.scss). Empty when shown
     * everywhere; $display is the value used where it is shown.
     */
    public static function displayClasses(bool $mobile, bool $tablet, bool $desktop, string $display = 'block'): string
    {
        if ($mobile && $tablet && $desktop) {
            return '';
        }

        return implode(' ', array_filter([
            $mobile ? "d-{$display}" : 'd-none',
            $tablet ? "d-md-{$display}" : 'd-md-none',
            $desktop ? "d-lg-{$display}" : 'd-lg-none',
            $tablet !== $desktop ? ($tablet ? "gsm-t-{$display}" : 'gsm-t-none') : null,
        ]));
    }
}
