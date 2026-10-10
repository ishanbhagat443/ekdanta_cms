<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\MotionPreset;
use App\Models\Section;
use App\Services\SectionTypes\SectionTypeRegistry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * A section's motion (the Motion tab).
 *
 * Stored in style.motion.{desktop,tablet,mobile} as sparse patches over the
 * section type's design defaults (SectionType::motionDefaults()), so "Reset
 * to design default" is deleting a patch, and revisions, saved blocks and
 * exports carry motion with the rest of the style. Resolved here into the
 * config the engine reads (resources/js/frontend/motion).
 *
 * Shape of one device:
 *   off    bool                      no motion on this device
 *   stage  {pin, length}             pinned full-screen stage, scroll length in design px
 *   steps  {part, range, dwell}      items that pause in turn (sets is-active / aria-current)
 *   scene  {params}                  typed values for a hand-coded scene
 *   tracks {id: {part, children, trigger, preset | keyframes, params, duration,
 *                delay, ease, stagger, range, step, reduce, once, enabled}}
 *
 * Everything here is data: keyframes are JSON, never code. Values are
 * whitelisted and clamped; strings can't carry ;{}<> url() or script.
 */
final class SectionMotion
{
    public const DEVICES = ['desktop', 'tablet', 'mobile'];

    public const TRIGGERS = [
        'scrub' => 'Tied to scrolling',
        'enter' => 'When it scrolls into view',
        'load' => 'When the page opens',
        'loop' => 'Loops',
        'hover' => 'On hover or keyboard focus',
        'click' => 'On click',
        'state' => 'When its step is active',
    ];

    /** The 9 entrance effects of the old Animation settings (same slugs in the Motion Library). */
    public const ENTRANCE = [
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

    public const EASES = [
        'outQuad' => 'Smooth', 'outCubic' => 'Smooth, longer tail', 'outExpo' => 'Fast then glide',
        'outBack' => 'Slight overshoot', 'inOutQuad' => 'Ease in and out', 'inOutCubic' => 'Ease in and out, stronger',
        'linear' => 'Linear (steady)', 'spring' => 'Springy',
    ];

    private const CSS_EASE = '/^(linear|ease|ease-in|ease-out|ease-in-out|step-start|step-end|steps\(\d{1,3}(,\s*(start|end|jump-none|jump-both))?\)|cubic-bezier\((-?\d*\.?\d+,\s*){3}-?\d*\.?\d+\))$/';

    private const ANIME_EASE = '/^(linear|(in|out|inOut|outIn)(Quad|Cubic|Quart|Quint|Sine|Expo|Circ|Back|Elastic|Bounce)?(\(\d{1,2}(\.\d+)?\))?|steps\(\d{1,3}\)|cubicBezier\((-?\d*\.?\d+,\s*){3}-?\d*\.?\d+\)|spring(\(\d{1,4}(\.\d+)?,\s*\d{1,4}(\.\d+)?\))?)$/';

    /** Kept whole when an edit is compared field by field: a half-changed keyframe set is not a keyframe set. */
    private const ATOMIC = ['keyframes', 'range'];

    private static ?array $presetCache = null;

    /* ── Reading ──────────────────────────────────────────────────────── */

    /** The stored patches, or ones derived from the older settings (style.anim, the Hero's entrance). */
    public static function stored(Section $section): array
    {
        $style = (array) $section->style;

        return is_array($style['motion'] ?? null)
            ? $style['motion']
            : self::fromLegacy($style, (array) $section->data_desktop, (string) $section->type);
    }

    /**
     * What each device actually runs: design defaults + patches.
     * Mobile starts from the type's mobile design (or desktop); tablet from
     * the type's tablet design, else its mobile one (768–991 uses the phone
     * composition), else desktop.
     *
     * @return array<string, array>
     */
    public static function effective(Section $section): array
    {
        $registry = app(SectionTypeRegistry::class);
        $defaults = $registry->has($section->type) ? $registry->find($section->type)->motionDefaults() : [];
        $patch = self::stored($section);

        $desktop = self::merge(self::sanitiseDevice((array) ($defaults['desktop'] ?? [])), (array) ($patch['desktop'] ?? []));
        $mobile = self::merge(isset($defaults['mobile']) ? self::sanitiseDevice($defaults['mobile']) : $desktop, (array) ($patch['mobile'] ?? []));
        $tabletBase = isset($defaults['tablet']) ? self::sanitiseDevice($defaults['tablet']) : (isset($defaults['mobile']) ? $mobile : $desktop);
        $tablet = self::merge($tabletBase, (array) ($patch['tablet'] ?? []));

        return compact('desktop', 'tablet', 'mobile');
    }

    /** Convert the older settings into motion patches (read time and the data migration share this). */
    public static function fromLegacy(array $style, array $data, string $type): array
    {
        $motion = [];
        $anim = is_array($style['anim'] ?? null) ? $style['anim']
            : (is_string($style['animation'] ?? null) ? ['desktop' => ['preset' => $style['animation']]] : []);

        foreach (self::DEVICES as $device) {
            $values = is_array($anim[$device] ?? null) ? $anim[$device] : [];
            $preset = (string) ($values['preset'] ?? '');

            if ($preset === '' || $preset === 'inherit') {
                continue; // follows desktop
            }

            $stagger = (int) ($values['stagger'] ?? 0);
            $motion[$device]['tracks']['enter'] = $preset !== 'none' && isset(self::ENTRANCE[$preset])
                ? self::sanitiseTrack([
                    'part' => '@self', 'trigger' => 'enter', 'preset' => $preset, 'children' => $stagger > 0,
                    'duration' => $values['duration'] ?? 700, 'delay' => $values['delay'] ?? 0,
                    'ease' => $values['ease'] ?? 'outQuad', 'stagger' => $stagger,
                    'once' => (bool) ($values['once'] ?? true), 'enabled' => true,
                ])
                : ['enabled' => false];
        }

        // Hero Banner → "Content Entrance Animation" is now its content track.
        if ($type === 'hero' && isset($data['entrance']) && $data['entrance'] !== 'fade-up') {
            $motion['desktop']['tracks']['content'] = in_array($data['entrance'], ['fade-in', 'zoom-in'], true)
                ? ['preset' => 'hero-'.$data['entrance']]
                : ['enabled' => false];
        }

        return $motion;
    }

    /** One entrance effect on the whole section, on every device (new sections, page starters). */
    public static function entrance(string $preset, array $timing = []): array
    {
        return ['desktop' => ['tracks' => ['enter' => self::sanitiseTrack(['part' => '@self', 'trigger' => 'enter', 'preset' => $preset] + $timing)]]];
    }

    /**
     * Starting motion for a new page section: Theme Customizer → Animations
     * → Default Animation, or none. An unknown stored value means none.
     */
    public static function newSectionStyle(): array
    {
        $preset = (string) theme('animations.default_type', 'none');

        if ($preset === 'none' || ! isset(self::ENTRANCE[$preset])) {
            return [];
        }

        return ['motion' => self::entrance($preset, ['duration' => theme('animations.duration'), 'delay' => theme('animations.delay')])];
    }

    /* ── Saving (Motion tab) ──────────────────────────────────────────── */

    /**
     * Fold a Motion tab save into the stored patches. The form posts every
     * value it showed; only the ones the user changed become patches, so a
     * tablet tab nobody touched keeps following its base device. A device
     * that is not posted at all is left exactly as it is.
     * $resets: "desktop" (whole device) or "desktop.tracks.head" (one track).
     */
    public static function applyEdit(Section $section, array $posted, array $resets): array
    {
        $patch = self::stored($section);
        $shown = self::effective($section);

        foreach (self::DEVICES as $device) {
            if (! is_array($posted[$device] ?? null)) {
                continue;
            }

            $new = self::flatten(self::implicit(self::sanitiseDevice($posted[$device])));
            $old = self::flatten(self::implicit($shown[$device]));
            $mine = self::flatten((array) ($patch[$device] ?? []));

            foreach ($new as $path => $value) {
                if (! array_key_exists($path, $old) || $old[$path] !== $value) {
                    $mine[$path] = $value;
                }
            }
            // The form shows every field, so one that comes back empty was
            // emptied: it goes back to the design (or preset) default.
            foreach (array_diff_key($old, $new) as $path => $value) {
                unset($mine[$path]);
            }

            $patch[$device] = Arr::undot($mine);
        }

        foreach ($resets as $reset) {
            [$device, $path] = array_pad(explode('.', (string) $reset, 2), 2, null);

            if ($device === 'all') {
                return [];
            }
            if (! in_array($device, self::DEVICES, true)) {
                continue;
            }

            $mine = self::flatten((array) ($patch[$device] ?? []));
            foreach (array_keys($mine) as $key) {
                if ($path === null || $key === $path || str_starts_with($key, $path.'.')) {
                    unset($mine[$key]);
                }
            }
            $patch[$device] = Arr::undot($mine);
        }

        $clean = self::sanitiseStored($patch);
        self::assertPresetsExist($clean);

        return $clean;
    }

    private static function assertPresetsExist(array $motion): void
    {
        $known = self::presets();

        foreach ($motion as $device => $config) {
            foreach ((array) ($config['tracks'] ?? []) as $id => $track) {
                if (isset($track['preset']) && ! isset($known[$track['preset']])) {
                    throw ValidationException::withMessages(["motion.{$device}.tracks.{$id}.preset" => "There is no Motion Library preset called \"{$track['preset']}\"."]);
                }
            }
        }
    }

    /* ── Sanitising ───────────────────────────────────────────────────── */

    /** One device's config or patch: only known keys, every value checked. Missing keys stay missing. */
    public static function sanitiseDevice(array $in): array
    {
        $out = [];

        if (array_key_exists('off', $in)) {
            $out['off'] = self::bool($in['off']);
        }

        if (is_array($in['stage'] ?? null)) {
            $stage = [];
            if (array_key_exists('pin', $in['stage'])) {
                $stage['pin'] = self::bool($in['stage']['pin']);
            }
            if (is_numeric($in['stage']['length'] ?? null)) {
                $stage['length'] = self::int($in['stage']['length'], 0, 30000);
            }
            $out['stage'] = $stage;
        }

        if (is_array($in['steps'] ?? null)) {
            $steps = [];
            if (self::isPart($in['steps']['part'] ?? null)) {
                $steps['part'] = $in['steps']['part'];
            }
            if (($range = self::range($in['steps']['range'] ?? null)) !== null) {
                $steps['range'] = $range;
            }
            if (is_numeric($in['steps']['dwell'] ?? null)) {
                $steps['dwell'] = round(max(0, min(0.9, (float) $in['steps']['dwell'])), 3);
            }
            $out['steps'] = $steps;
        }

        if (is_array($in['scene']['params'] ?? null)) {
            $out['scene']['params'] = self::params($in['scene']['params']);
        }

        foreach ((array) ($in['tracks'] ?? []) as $id => $track) {
            if (is_array($track) && preg_match('/^[a-z0-9_-]{1,40}$/i', (string) $id) && count($out['tracks'] ?? []) < 60) {
                $out['tracks'][(string) $id] = self::sanitiseTrack($track);
            }
        }

        return $out;
    }

    /** A whole stored style.motion (imports, saved blocks): each device cleaned. */
    public static function sanitiseStored(array $motion): array
    {
        return array_filter(array_map(fn ($device) => self::sanitiseDevice((array) $device), array_intersect_key($motion, array_flip(self::DEVICES))));
    }

    public static function sanitiseTrack(array $t): array
    {
        $out = [];

        if (self::isPart($t['part'] ?? null)) {
            $out['part'] = $t['part'];
        }
        foreach (['children', 'once', 'enabled'] as $key) {
            if (array_key_exists($key, $t)) {
                $out[$key] = self::bool($t[$key]);
            }
        }
        if (isset(self::TRIGGERS[$t['trigger'] ?? ''])) {
            $out['trigger'] = $t['trigger'];
        }
        if (is_string($t['preset'] ?? null) && preg_match('/^[a-z0-9-]{1,80}$/', $t['preset'])) {
            $out['preset'] = $t['preset'];
        }
        if (($keyframes = self::keyframes($t['keyframes'] ?? null)) !== null) {
            $out['keyframes'] = $keyframes;
        }
        if (is_array($t['params'] ?? null)) {
            $out['params'] = self::params($t['params']);
        }
        foreach (['duration' => 60000, 'delay' => 60000, 'stagger' => 10000, 'step' => 50] as $key => $max) {
            if (is_numeric($t[$key] ?? null)) {
                $out[$key] = self::int($t[$key], 0, $max);
            }
        }
        if (is_string($t['ease'] ?? null) && (preg_match(self::ANIME_EASE, $t['ease']) || preg_match(self::CSS_EASE, $t['ease']))) {
            $out['ease'] = $t['ease'];
        }
        if (($range = self::range($t['range'] ?? null)) !== null) {
            $out['range'] = $range;
        }
        if (in_array($t['reduce'] ?? null, ['final', 'hide', 'step'], true)) {
            $out['reduce'] = $t['reduce'];
        }

        return $out;
    }

    /**
     * Anime.js keyframes: property => value | [values] | [{to, from, duration, delay, ease}].
     * Our markers: $split (words|chars), $count (true or a locale). A JSON string is decoded first.
     */
    public static function keyframes(mixed $value): ?array
    {
        if (is_string($value)) {
            $value = trim($value) === '' ? null : json_decode($value, true);
        }
        if (! is_array($value) || $value === [] || count($value) > 30) {
            return null;
        }

        $out = [];
        foreach ($value as $prop => $v) {
            if ($prop === '$split') {
                if (! in_array($v, ['words', 'chars'], true)) {
                    return null;
                }
            } elseif ($prop === '$count') {
                if (! ($v === true || (is_string($v) && preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $v)))) {
                    return null;
                }
            } elseif (! is_string($prop) || ! preg_match('/^(--[a-z0-9-]{1,40}|[a-zA-Z]{1,40})$/', $prop) || ($v = self::keyframeValue($v)) === null) {
                return null;
            }
            $out[$prop] = $v;
        }

        return $out;
    }

    /** CSS @keyframes as JSON: {"0%,100%": {"transform": "rotate(0deg)"}, …}. */
    public static function cssKeyframes(mixed $value): ?array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (! is_array($value) || $value === [] || count($value) > 40) {
            return null;
        }

        $out = [];
        foreach ($value as $selector => $declarations) {
            if (! is_string($selector) || ! preg_match('/^\s*((\d{1,3}(\.\d+)?%|from|to)\s*,\s*)*(\d{1,3}(\.\d+)?%|from|to)\s*$/', $selector) || ! is_array($declarations)) {
                return null;
            }
            foreach ($declarations as $prop => $v) {
                if (! is_string($prop) || ! preg_match('/^(--[a-z0-9-]{1,40}|-?[a-z][a-z-]{0,40})$/', $prop) || ! (is_numeric($v) || (is_string($v) && self::safe($v)))) {
                    return null;
                }
            }
            $out[trim($selector)] = $declarations;
        }

        return $out;
    }

    private static function keyframeValue(mixed $v, int $depth = 0): mixed
    {
        if (is_int($v) || is_float($v)) {
            return $v;
        }
        if (is_string($v)) {
            return self::safe($v) ? $v : null;
        }
        if (! is_array($v) || $v === [] || $depth > 1 || count($v) > 20) {
            return null;
        }

        $out = [];
        foreach ($v as $k => $item) {
            if (is_string($k) && ! in_array($k, ['to', 'from', 'duration', 'delay', 'ease'], true)) {
                return null;
            }
            if ($k === 'ease') {
                if (! is_string($item) || ! preg_match(self::ANIME_EASE, $item)) {
                    return null;
                }
                $out[$k] = $item;
                continue;
            }
            if (($clean = self::keyframeValue($item, $depth + 1)) === null) {
                return null;
            }
            $out[$k] = $clean;
        }

        return $out;
    }

    /** A value that can't end a declaration, open a block, load a URL or run script. {param} placeholders are fine. */
    private static function safe(string $v): bool
    {
        $v = preg_replace('/\{[a-zA-Z][a-zA-Z0-9_]{0,30}\}/', '', $v);

        return strlen($v) <= 200 && ! preg_match('/[;{}<>\\\\"`]|url\s*\(|expression|javascript:|@import/i', $v);
    }

    public static function params(array $params): array
    {
        $out = [];
        foreach ($params as $name => $value) {
            if (count($out) >= 20 || ! preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,30}$/', (string) $name)) {
                continue;
            }
            if (is_numeric($value)) {
                $out[$name] = $value + 0;
            } elseif (is_string($value) && strlen($value) <= 100 && self::safe($value)) {
                $out[$name] = $value;
            }
        }

        return $out;
    }

    /** An ease a preset of this kind can use: CSS timing functions for css, Anime.js names for anime. */
    public static function validEase(string $ease, string $kind): bool
    {
        return preg_match($kind === 'css' ? self::CSS_EASE : self::ANIME_EASE, $ease) === 1;
    }

    private static function isPart(mixed $part): bool
    {
        return is_string($part) && preg_match('/^(@self|[a-z0-9_-]{1,40})$/i', $part) === 1;
    }

    private static function range(mixed $range): ?array
    {
        $range = is_array($range) ? array_values($range) : null;

        if ($range === null || count($range) !== 2 || ! is_numeric($range[0]) || ! is_numeric($range[1])) {
            return null;
        }

        $a = self::int($range[0], -20000, 60000);
        $b = self::int($range[1], -20000, 60000);

        return $b > $a ? [$a, $b] : null;
    }

    private static function int(mixed $v, int $min, int $max): int
    {
        return max($min, min($max, (int) round((float) $v)));
    }

    private static function bool(mixed $v): bool
    {
        return filter_var($v, FILTER_VALIDATE_BOOLEAN);
    }

    /* ── Rendering ────────────────────────────────────────────────────── */

    /**
     * What the page needs for one section, or null when it has no motion:
     * config (per device, presets resolved), CSS (@keyframes for its CSS
     * presets, plus the pre-script starting state), stage lengths.
     */
    public static function render(Section $section): ?array
    {
        $presets = self::presets();
        $config = [];
        $css = '';
        $lengths = [];
        $media = ['desktop' => '(min-width: 992px)', 'tablet' => '(min-width: 768px) and (max-width: 991.98px)', 'mobile' => '(max-width: 767.98px)'];

        foreach (self::effective($section) as $device => $effective) {
            $out = [];

            if (! empty($effective['off'])) {
                $config[$device] = ['off' => true];
                continue;
            }

            $length = (int) ($effective['stage']['length'] ?? 0);
            if ($length > 0) {
                $out['stage'] = ['pin' => (bool) ($effective['stage']['pin'] ?? false), 'length' => $length];
                if ($out['stage']['pin']) {
                    $lengths[$device] = $length;
                }
            }
            if (isset($effective['steps']['part'], $effective['steps']['range'])) {
                $out['steps'] = $effective['steps'] + ['dwell' => 0.35];
            }
            if (! empty($effective['scene']['params'])) {
                $out['scene'] = $effective['scene'];
            }

            $hidden = [];
            foreach ((array) ($effective['tracks'] ?? []) as $id => $track) {
                if (($resolved = self::resolve($track, $presets)) === null) {
                    continue;
                }
                $out['tracks'][] = array_diff_key(['id' => (string) $id] + $resolved, ['cssPreset' => true]);

                if (isset($resolved['cssPreset'])) {
                    $css .= self::keyframesCss($presets[$resolved['cssPreset']]);
                }
                if (in_array($resolved['trigger'], ['enter', 'load'], true) && self::startsHidden($resolved, $presets)) {
                    $hidden[] = $resolved['part'] === '@self'
                        ? ''
                        : '[data-m="'.$resolved['part'].'"]'.(! empty($resolved['children']) ? ' > *' : '');
                }
            }

            // Until the script takes over, parts that fade in start hidden, so
            // nothing flashes. Not without JS, not under reduced motion, and
            // the script (or a timeout) lifts it: see .ek-revealed / .ek-reveal.
            if ($hidden !== []) {
                $scope = 'html.js-enabled:not(.ek-reveal):not([data-motion="off"]) #gsm-s'.$section->id.':not(.ek-revealed)';
                $css .= '@media (prefers-reduced-motion: no-preference) and '.$media[$device].'{'
                    .implode(',', array_map(fn ($sel) => trim($scope.' '.$sel), array_unique($hidden))).'{opacity:0}}';
            }

            $config[$device] = $out;
        }

        if (array_filter($config) === []) {
            return null;
        }

        // Stages that stay pinned under reduced motion: steps, and "step" tracks.
        $steps = array_filter(array_map(fn ($device) => isset($device['steps'])
            || in_array('step', array_column((array) ($device['tracks'] ?? []), 'reduce'), true), $config));

        return ['config' => $config, 'css' => $css, 'lengths' => $lengths, 'steps' => $steps !== []];
    }

    private static function resolve(array $track, array $presets): ?array
    {
        if (($track['enabled'] ?? true) === false || ! isset($track['part'])) {
            return null;
        }

        $preset = isset($track['preset']) ? ($presets[$track['preset']] ?? null) : null;
        $trigger = $track['trigger'] ?? $preset['trigger'] ?? 'enter';
        $out = ['part' => $track['part'], 'trigger' => $trigger];

        if (isset($track['keyframes'])) {
            $out['keyframes'] = $track['keyframes'];
        } elseif ($preset !== null) {
            $params = array_replace((array) ($preset['params'] ?? []), (array) ($track['params'] ?? []));

            if ($preset['kind'] === 'css') {
                if ($trigger === 'scrub') {
                    return null; // CSS keyframes run on time, not on scroll
                }
                $out['css'] = 'ek-'.$preset['slug'];
                $out['cssPreset'] = $preset['slug'];
                if ($params !== []) {
                    $out['vars'] = $params;
                }
            } else {
                $out['keyframes'] = self::fill((array) $preset['keyframes'], $params);
            }
        } else {
            return null;
        }

        if ($trigger === 'scrub' && ! isset($track['range'])) {
            return null;
        }

        $ease = $track['ease'] ?? $preset['ease'] ?? null;
        if (isset($out['css']) && $ease !== null && ! preg_match(self::CSS_EASE, $ease)) {
            $ease = $preset['ease'] ?? 'ease';
        }

        $out += array_filter([
            'children' => ! empty($track['children']) ?: null,
            'duration' => $track['duration'] ?? $preset['duration'] ?? null,
            'delay' => $track['delay'] ?? $preset['delay'] ?? null,
            'ease' => $ease,
            'stagger' => $track['stagger'] ?? $preset['stagger'] ?? null,
            'range' => $track['range'] ?? null,
            'step' => $track['step'] ?? null,
            'reduce' => $track['reduce'] ?? null,
            'once' => ($track['once'] ?? true) === false ? false : null,
        ], fn ($v) => $v !== null);

        return $out;
    }

    /** {param} placeholders: a value that is exactly "{name}" becomes the number itself. */
    private static function fill(array $keyframes, array $params): array
    {
        array_walk_recursive($keyframes, function (&$value) use ($params): void {
            if (! is_string($value)) {
                return;
            }
            if (preg_match('/^\{([a-zA-Z][a-zA-Z0-9_]*)\}$/', $value, $m) && array_key_exists($m[1], $params)) {
                $value = $params[$m[1]];

                return;
            }
            $value = preg_replace_callback('/\{([a-zA-Z][a-zA-Z0-9_]*)\}/', fn ($m) => (string) ($params[$m[1]] ?? $m[0]), $value);
        });

        return $keyframes;
    }

    private static function startsHidden(array $track, array $presets): bool
    {
        if (isset($track['cssPreset'])) {
            foreach ((array) $presets[$track['cssPreset']]['keyframes'] as $selector => $declarations) {
                if (preg_match('/(^|,)\s*(0%|from)\s*(,|$)/', (string) $selector)) {
                    return (string) ($declarations['opacity'] ?? '1') === '0';
                }
            }

            return false;
        }

        $opacity = $track['keyframes']['opacity'] ?? null;
        $first = is_array($opacity) ? ($opacity[0] ?? $opacity['from'] ?? null) : null;
        $first = is_array($first) ? ($first['from'] ?? $first['to'] ?? null) : $first;

        return $first !== null && (float) $first === 0.0;
    }

    /** "@keyframes ek-<slug>{…}", once per response (remembered on the request, not the process). */
    private static function keyframesCss(array $preset): string
    {
        $printed = request()->attributes->get('ek.keyframes', []);

        if (isset($printed[$preset['slug']]) || ($frames = self::cssKeyframes($preset['keyframes'])) === null) {
            return '';
        }
        request()->attributes->set('ek.keyframes', $printed + [$preset['slug'] => true]);

        $css = '';
        foreach ($frames as $selector => $declarations) {
            $css .= $selector.'{'.implode(';', array_map(fn ($p, $v) => $p.':'.$v, array_keys($declarations), $declarations)).'}';
        }

        return '@keyframes ek-'.$preset['slug'].'{'.$css.'}';
    }

    /** Every Motion Library preset, keyed by slug (cached; MotionPreset flushes it on save). */
    public static function presets(): array
    {
        return self::$presetCache ??= Cache::rememberForever(MotionPreset::CACHE_KEY, fn () => MotionPreset::query()
            ->get(['slug', 'name', 'kind', 'trigger', 'keyframes', 'params', 'duration', 'delay', 'ease', 'stagger'])
            ->keyBy('slug')
            ->map(fn (MotionPreset $preset) => $preset->toArray())
            ->all());
    }

    public static function forgetPresets(): void
    {
        self::$presetCache = null;
        Cache::forget(MotionPreset::CACHE_KEY);
    }

    /* ── Helpers ──────────────────────────────────────────────────────── */

    /** Merge a patch over a device config: tracks by id, key by key; keyframes and ranges whole. */
    private static function merge(array $base, array $patch): array
    {
        $patch = self::sanitiseDevice($patch);

        if (array_key_exists('off', $patch)) {
            $base['off'] = $patch['off'];
        }
        foreach (['stage', 'steps'] as $key) {
            if (isset($patch[$key])) {
                $base[$key] = array_replace((array) ($base[$key] ?? []), $patch[$key]);
            }
        }
        if (isset($patch['scene']['params'])) {
            $base['scene']['params'] = array_replace((array) ($base['scene']['params'] ?? []), $patch['scene']['params']);
        }
        foreach ((array) ($patch['tracks'] ?? []) as $id => $track) {
            $old = (array) ($base['tracks'][$id] ?? []);
            $merged = array_replace($old, $track);
            if (isset($old['params'], $track['params'])) {
                $merged['params'] = array_replace($old['params'], $track['params']);
            }
            $base['tracks'][$id] = $merged;
        }

        return $base;
    }

    /**
     * Switches the form always sends, filled in where a config leaves them
     * out, so "on" posted for a track that was simply on is not an edit.
     */
    private static function implicit(array $config): array
    {
        $config += ['off' => false];
        $config['stage'] = (array) ($config['stage'] ?? []) + ['pin' => false];
        foreach ((array) ($config['tracks'] ?? []) as $id => $track) {
            $config['tracks'][$id] = $track + ['enabled' => true, 'once' => true, 'children' => false, 'reduce' => 'final'];
        }

        return $config;
    }

    /** "tracks.head.duration" => 700, with keyframes and ranges kept whole. */
    private static function flatten(array $values, string $prefix = ''): array
    {
        $out = [];
        foreach ($values as $key => $value) {
            $path = $prefix.$key;
            if (is_array($value) && $value !== [] && ! in_array((string) $key, self::ATOMIC, true)) {
                $out += self::flatten($value, $path.'.');
            } else {
                $out[$path] = $value;
            }
        }

        return $out;
    }
}
