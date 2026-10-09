<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Scroll animation settings for a section.
 *
 * One source of truth shared by the editor (which renders the dropdowns) and
 * the frontend (which renders the data-* attributes gsm-animations.js reads).
 * The admin never writes JavaScript: they pick a preset, and the numbers ride
 * along as attributes.
 *
 * Presets animate transform and opacity only. Animating layout properties
 * (height, margin, top) forces reflow on every frame and janks on mid-range
 * phones, which is exactly the audience this feature is for.
 */
final class SectionAnimation
{
    public const DEVICES = ['desktop', 'tablet', 'mobile'];

    /**
     * Marks a tablet/mobile tab as "whatever desktop does". It is the default
     * for those tabs, so choosing an effect on Desktop alone animates every
     * screen — the thing a non-designer expects, and what they get.
     */
    public const INHERIT = 'inherit';

    /** Preset key => admin label. "none" is the default and ships no attributes. */
    public const PRESETS = [
        'none' => 'None',
        'fade-in' => 'Fade in',
        'fade-up' => 'Fade up',
        'fade-down' => 'Fade down',
        'fade-left' => 'Fade in from left',
        'fade-right' => 'Fade in from right',
        'zoom-in' => 'Zoom in',
        'zoom-out' => 'Zoom out',
        'flip-up' => 'Flip up',
    ];

    /**
     * Easing curves, in the order a non-designer should meet them: the calm
     * ones first, the showy ones last.
     */
    public const EASINGS = [
        'outQuad' => 'Smooth (default)',
        'outCubic' => 'Smooth, longer tail',
        'outExpo' => 'Fast then glide',
        'outBack' => 'Slight overshoot',
        'inOutQuad' => 'Ease in and out',
        'spring' => 'Springy',
    ];

    public const DEFAULTS = [
        'preset' => 'none',
        'duration' => 700,   // ms
        'delay' => 0,        // ms
        'ease' => 'outQuad',
        'stagger' => 0,      // ms between children; 0 = animate the section as one
        'once' => true,      // replay on every scroll-in when false
    ];

    public const DURATION_RANGE = [150, 2500];
    public const DELAY_RANGE = [0, 2000];
    public const STAGGER_RANGE = [0, 400];

    /**
     * Read one device's settings out of a section's style JSON.
     *
     * Shape in storage (mirrors how `visible` and `width` are already stored):
     *   style.anim.desktop = ['preset' => 'fade-up', 'duration' => 700, …]
     *
     * Tablet and mobile fall back to desktop, so the common case — one effect
     * everywhere — needs the editor to fill in exactly one tab.
     *
     * @param  array<string, mixed>  $style
     * @return array<string, mixed>
     */
    public static function forDevice(array $style, string $device): array
    {
        $anim = is_array($style['anim'] ?? null) ? $style['anim'] : [];

        $values = is_array($anim[$device] ?? null) ? $anim[$device] : [];

        // Unset, or explicitly "same as desktop": take the desktop settings.
        $inherits = $values === [] || ($values['preset'] ?? null) === self::INHERIT;

        if ($inherits && $device !== 'desktop') {
            $values = is_array($anim['desktop'] ?? null) ? $anim['desktop'] : [];
        }

        // Pre-v0.3 sections stored a single `style.animation` string. Honour it
        // so animations set before the upgrade keep playing.
        if ($values === [] && is_string($style['animation'] ?? null)) {
            $values = ['preset' => $style['animation']];
        }

        // Whatever device was asked for, the answer is always a real preset:
        // an unresolved "inherit" (desktop, or a stored value from an older
        // or crafted payload) reads as "no animation".
        return self::sanitise($values);
    }

    /**
     * Clamp and whitelist one device's settings.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public static function sanitise(array $values, bool $allowInherit = false): array
    {
        $preset = (string) ($values['preset'] ?? self::DEFAULTS['preset']);

        // "inherit" only means something on a device that HAS something to
        // inherit from. On desktop it is not a preset, and letting it through
        // would put a meaningless data-anim="inherit" on the page.
        $known = array_key_exists($preset, self::PRESETS)
            || ($allowInherit && $preset === self::INHERIT);

        if (! $known) {
            $preset = self::DEFAULTS['preset'];
        }

        $ease = (string) ($values['ease'] ?? self::DEFAULTS['ease']);

        if (! array_key_exists($ease, self::EASINGS)) {
            $ease = self::DEFAULTS['ease'];
        }

        return [
            'preset' => $preset,
            'duration' => self::clamp($values['duration'] ?? null, self::DURATION_RANGE, self::DEFAULTS['duration']),
            'delay' => self::clamp($values['delay'] ?? null, self::DELAY_RANGE, self::DEFAULTS['delay']),
            'ease' => $ease,
            'stagger' => self::clamp($values['stagger'] ?? null, self::STAGGER_RANGE, self::DEFAULTS['stagger']),
            'once' => (bool) ($values['once'] ?? self::DEFAULTS['once']),
        ];
    }

    /**
     * Sanitise a whole submitted `anim` map, device by device.
     *
     * @param  array<string, mixed>  $submitted
     * @return array<string, array<string, mixed>>
     */
    /** One animation on desktop, with tablet and mobile following it. */
    public static function everyDevice(array $desktop): array
    {
        return self::sanitiseAll([
            'desktop' => $desktop,
            'tablet' => ['preset' => self::INHERIT],
            'mobile' => ['preset' => self::INHERIT],
        ]);
    }

    /**
     * Starting style for a new page section: Theme Customizer → Animations
     * → Default Animation, or nothing. An unknown stored value (the old
     * fade/zoom/slide/bounce list, which never animated anything) means none.
     */
    public static function newSectionStyle(): array
    {
        $preset = (string) theme('animations.default_type', 'none');

        if ($preset === 'none' || ! isset(self::PRESETS[$preset])) {
            return [];
        }

        return ['anim' => self::everyDevice([
            'preset' => $preset,
            'duration' => theme('animations.duration'),
            'delay' => theme('animations.delay'),
        ])];
    }

    public static function sanitiseAll(array $submitted): array
    {
        $clean = [];

        foreach (self::DEVICES as $device) {
            $values = is_array($submitted[$device] ?? null) ? $submitted[$device] : [];

            // Store the intent, not a copy of the desktop numbers: a later
            // change to Desktop should follow through to the tabs that say
            // "same as desktop", instead of leaving them frozen.
            if ($device !== 'desktop' && ($values['preset'] ?? null) === self::INHERIT) {
                $clean[$device] = ['preset' => self::INHERIT];

                continue;
            }

            $clean[$device] = self::sanitise($values, $device !== 'desktop');
        }

        return $clean;
    }

    /** True when this device tab is set to follow desktop. */
    public static function inherits(array $style, string $device): bool
    {
        if ($device === 'desktop') {
            return false;
        }

        $anim = is_array($style['anim'] ?? null) ? $style['anim'] : [];
        $values = is_array($anim[$device] ?? null) ? $anim[$device] : [];

        return $values === [] || ($values['preset'] ?? null) === self::INHERIT;
    }

    /**
     * The data-* attributes for a section wrapper.
     *
     * Every device that differs from desktop gets its own attribute, so one
     * section can carry a big effect on desktop and a plain fade on a phone —
     * gsm-animations.js picks by viewport at run time.
     *
     * Returns [] when nothing is animated: no attributes, no hidden state, no
     * work for the script.
     *
     * @param  array<string, mixed>  $style
     * @return array<string, string>
     */
    public static function attributesFor(array $style): array
    {
        $desktop = self::forDevice($style, 'desktop');
        $tablet = self::forDevice($style, 'tablet');
        $mobile = self::forDevice($style, 'mobile');

        $animated = array_filter(
            ['desktop' => $desktop, 'tablet' => $tablet, 'mobile' => $mobile],
            fn (array $values) => $values['preset'] !== 'none'
        );

        if ($animated === []) {
            return [];
        }

        $attributes = [
            'data-anim' => $desktop['preset'],
            'data-anim-duration' => (string) $desktop['duration'],
            'data-anim-delay' => (string) $desktop['delay'],
            'data-anim-ease' => $desktop['ease'],
            'data-anim-stagger' => (string) $desktop['stagger'],
            'data-anim-once' => $desktop['once'] ? '1' : '0',
        ];

        foreach (['tablet' => $tablet, 'mobile' => $mobile] as $device => $values) {
            if ($values === $desktop) {
                continue; // inherits; no attribute needed
            }

            $attributes['data-anim-'.$device] = $values['preset'];
            $attributes['data-anim-'.$device.'-duration'] = (string) $values['duration'];
            $attributes['data-anim-'.$device.'-delay'] = (string) $values['delay'];
            $attributes['data-anim-'.$device.'-ease'] = $values['ease'];
            $attributes['data-anim-'.$device.'-stagger'] = (string) $values['stagger'];
            $attributes['data-anim-'.$device.'-once'] = $values['once'] ? '1' : '0';
        }

        return $attributes;
    }

    private static function clamp(mixed $value, array $range, int $default): int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return $default;
        }

        return (int) max($range[0], min($range[1], (int) $value));
    }
}
