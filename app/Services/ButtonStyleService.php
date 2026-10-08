<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ButtonStyle;
use App\Models\Media;
use App\Repositories\Contracts\ButtonStyleRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Button Builder engine: compiles every active ButtonStyle into a
 * .gsm-btn-{slug} CSS rule set (typography, per-device padding, free-form
 * per-corner radius, any background mode, hover state, animations,
 * unlimited shadows). Embedded into the Theme Customizer stylesheet.
 */
class ButtonStyleService
{
    public const CACHE_KEY = 'button-styles.active';

    public function __construct(
        protected ButtonStyleRepositoryInterface $styles,
        protected GradientService $gradients,
    ) {
    }

    /** @return Collection<int, ButtonStyle> */
    public function active(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => $this->styles->allActive());
    }

    public function findBySlug(?string $slug): ?ButtonStyle
    {
        return $slug ? $this->active()->firstWhere('slug', $slug) : null;
    }

    public function create(array $data): ButtonStyle
    {
        $data['slug'] = $data['slug'] ?? $this->uniqueSlug($data['name']);
        $style = $this->styles->create($data);
        $this->flushCache();

        return $style;
    }

    public function update(ButtonStyle $style, array $data): ButtonStyle
    {
        $style = $this->styles->update($style, $data);
        $this->flushCache();

        return $style;
    }

    public function delete(ButtonStyle $style): void
    {
        $this->styles->delete($style);
        $this->flushCache();
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(ThemeService::CSS_CACHE_KEY);
    }

    /* ── CSS compilation ─────────────────────────────────────────── */

    /** Stylesheet for every active button style + shared animation bits. */
    public function css(): string
    {
        $css = $this->sharedCss();

        foreach ($this->active() as $style) {
            $css .= $this->styleCss($style);
        }

        return $css;
    }

    /** CSS for one style — also used by the admin live preview. */
    public function styleCss(ButtonStyle $style): string
    {
        $sel = '.'.$style->cssClass();
        $c = $style->config ?? [];

        $base = 'display:inline-flex;align-items:center;justify-content:center;text-decoration:none;'.
            'position:relative;overflow:hidden;cursor:pointer;';
        $base .= 'gap:'.(int) data_get($c, 'icon.gap', 8).'px;';
        $base .= $this->typographyCss(data_get($c, 'typography', []));
        $base .= $this->paddingCss(data_get($c, 'padding.desktop', []));
        $base .= $this->borderCss(data_get($c, 'border', []));
        $base .= $this->radiusCss(data_get($c, 'border.radius', []));
        $base .= $this->backgroundCss(data_get($c, 'background', []));
        $base .= $this->shadowsCss(data_get($c, 'shadows', []));
        $duration = (int) data_get($c, 'animation.duration', 250);
        $base .= "transition:all {$duration}ms ease;";

        $css = $sel.'{'.$base.'}';

        // Tablet / mobile padding overrides.
        foreach (['tablet' => '@media (max-width: 991.98px)', 'mobile' => '@media (max-width: 767.98px)'] as $device => $query) {
            $pad = $this->paddingCss(data_get($c, "padding.{$device}", []));
            if ($pad !== '') {
                $css .= $query.'{'.$sel.'{'.$pad.'}}';
            }
        }

        // Hover state: background / border / radius / shadow / color overrides.
        $hover = data_get($c, 'hover', []);
        $hoverCss = $this->backgroundCss($hover, onlyWhenSet: true);
        $hoverCss .= $this->borderCss(data_get($hover, 'border', []), onlyWhenSet: true);
        $hoverCss .= $this->radiusCss(data_get($hover, 'radius', []));
        $hoverCss .= $this->shadowsCss(data_get($hover, 'shadows', []));
        $hoverCss .= $this->hoverEffectCss((string) data_get($c, 'animation.hover_effect', 'none'), $duration);

        if ($hoverCss !== '') {
            $css .= $sel.':hover{'.$hoverCss.'}';
        }

        return $css;
    }

    /** Shared keyframes + ripple/glow plumbing, emitted once. */
    private function sharedCss(): string
    {
        return '@keyframes gsm-btn-pulse{0%,100%{transform:scale(1);}50%{transform:scale(1.045);}}'.
            '@keyframes gsm-btn-ripple{to{transform:scale(3.5);opacity:0;}}'.
            '.gsm-btn-ripple-ink{position:absolute;border-radius:50%;transform:scale(0);'.
            'animation:gsm-btn-ripple .6s linear;background:rgba(255,255,255,.45);pointer-events:none;}';
    }

    private function typographyCss(array $t): string
    {
        $css = '';

        if ($family = $t['family'] ?? null) {
            // "role:buttons" → Theme Customizer font role variable.
            $css .= str_starts_with((string) $family, 'role:')
                ? 'font-family:var(--gsm-font-'.str_replace('_', '-', substr((string) $family, 5)).');'
                : 'font-family:'.$family.';';
        }
        if (($t['size'] ?? null) !== null && $t['size'] !== '') {
            $css .= 'font-size:'.(float) $t['size'].'px;';
        }
        if ($weight = $t['weight'] ?? null) {
            $css .= 'font-weight:'.$weight.';';
        }
        if (($t['letter_spacing'] ?? null) !== null && $t['letter_spacing'] !== '') {
            $css .= 'letter-spacing:'.(float) $t['letter_spacing'].'px;';
        }
        if ($transform = $t['transform'] ?? null) {
            $css .= 'text-transform:'.$transform.';';
        }
        if (($t['line_height'] ?? null) !== null && $t['line_height'] !== '') {
            $css .= 'line-height:'.(float) $t['line_height'].';';
        }

        return $css;
    }

    private function paddingCss(array $p): string
    {
        $sides = ['top', 'right', 'bottom', 'left'];
        $set = array_filter($p, fn ($v, $k) => in_array($k, $sides, true) && $v !== null && $v !== '', ARRAY_FILTER_USE_BOTH);

        if ($set === []) {
            return '';
        }

        $css = '';
        foreach ($sides as $side) {
            if (array_key_exists($side, $set)) {
                $css .= "padding-{$side}:".(float) $set[$side].'px;';
            }
        }

        return $css;
    }

    private function borderCss(array $b, bool $onlyWhenSet = false): string
    {
        $width = $b['width'] ?? null;
        $style = $b['style'] ?? null;
        $color = $b['color'] ?? null;

        if ($width === null || $width === '') {
            return $onlyWhenSet ? '' : 'border:0;';
        }

        return 'border:'.(float) $width.'px '.($style ?: 'solid').' '.($color ?: 'transparent').';';
    }

    /** Per-corner radius; values are free CSS lengths (0px, 20px, 50%, 999px…). */
    private function radiusCss(array $r): string
    {
        $corners = ['tl' => 'top-left', 'tr' => 'top-right', 'br' => 'bottom-right', 'bl' => 'bottom-left'];
        $css = '';

        foreach ($corners as $key => $corner) {
            $value = $r[$key] ?? null;
            if ($value !== null && $value !== '' && preg_match('/^\d+(\.\d+)?(px|%|rem|em)?$/', (string) $value)) {
                $unit = preg_match('/(px|%|rem|em)$/', (string) $value) ? '' : 'px';
                $css .= "border-{$corner}-radius:{$value}{$unit};";
            }
        }

        return $css;
    }

    private function backgroundCss(array $bg, bool $onlyWhenSet = false): string
    {
        $mode = $bg['mode'] ?? null;
        $css = '';

        if ($textColor = $bg['text_color'] ?? null) {
            $css .= 'color:'.$textColor.';';
        }

        if ($mode === null || $mode === '') {
            return $onlyWhenSet ? $css : $css.'background:transparent;';
        }

        $css .= match ($mode) {
            'solid' => 'background:'.($bg['color'] ?? 'transparent').';',
            'gradient' => ($slug = $bg['gradient_slug'] ?? null)
                ? 'background-image:var(--gsm-gradient-'.$slug.');'
                : '',
            'image' => ($id = $bg['media_id'] ?? null) && ($media = Media::query()->find((int) $id))
                ? "background-image:url('{$media->url}');background-size:cover;background-position:center;"
                : '',
            'transparent' => 'background:transparent;',
            // video backgrounds are layered by the <x-gsm-button> component;
            // keep the CSS side dark so text stays readable while loading.
            'video' => 'background:#101828;',
            default => '',
        };

        return $css;
    }

    /** Unlimited shadow rows → one combined box-shadow. */
    private function shadowsCss(array $shadows): string
    {
        $parts = collect($shadows)
            ->filter(fn ($s) => is_array($s) && ($s['color'] ?? null))
            ->map(function (array $s): string {
                $color = \App\Models\Gradient::stopColor(['color' => $s['color'], 'opacity' => $s['opacity'] ?? 100]);

                return (! empty($s['inset']) ? 'inset ' : '')
                    .(float) ($s['x'] ?? 0).'px '
                    .(float) ($s['y'] ?? 0).'px '
                    .(float) ($s['blur'] ?? 0).'px '
                    .(float) ($s['spread'] ?? 0).'px '
                    .$color;
            });

        return $parts->isEmpty() ? '' : 'box-shadow:'.$parts->implode(', ').';';
    }

    private function hoverEffectCss(string $effect, int $duration): string
    {
        return match ($effect) {
            'scale' => 'transform:scale(1.05);',
            'lift' => 'transform:translateY(-3px);',
            'slide' => 'transform:translateX(4px);',
            'glow' => 'filter:brightness(1.1) drop-shadow(0 0 12px rgba(255,255,255,.35));',
            'pulse' => "animation:gsm-btn-pulse {$duration}ms ease-in-out infinite;",
            default => '', // none + ripple (ripple is JS-driven, see frontend/app.js)
        };
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'button';
        $slug = $base;
        $i = 2;

        while (ButtonStyle::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
