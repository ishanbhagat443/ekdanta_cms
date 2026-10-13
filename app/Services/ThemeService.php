<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ThemeFont;
use App\Models\ThemePreset;
use App\Repositories\Contracts\ThemeRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ThemeService
{
    public const CACHE_KEY = 'theme.all';

    public const CSS_CACHE_KEY = 'theme.css';

    public function __construct(
        protected ThemeRepositoryInterface $theme,
        protected GradientService $gradients,
        protected ButtonStyleService $buttonStyles,
    ) {
    }

    /** Get one value by "group.key" notation, falling back to config default. */
    public function get(string $dottedKey, mixed $default = null): mixed
    {
        if (array_key_exists($dottedKey, $this->cached())) {
            $value = $this->cached()[$dottedKey];

            return $value === null ? $default : $value;
        }

        return $default ?? $this->configDefault($dottedKey);
    }

    /** All values of a group as [key => value], config defaults filled in. */
    public function group(string $group): array
    {
        $prefix = $group.'.';
        $result = [];

        foreach ($this->cached() as $dotted => $value) {
            if (str_starts_with($dotted, $prefix)) {
                $result[substr($dotted, strlen($prefix))] = $value;
            }
        }

        return $result;
    }

    /**
     * Persist a whole settings group from validated input.
     * Mirrors SettingsService::updateGroup — text/number/toggle/code/color
     * fields come as scalars; typography roles are handled by updateTypography().
     */
    public function updateGroup(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue; // typography roles handled separately (nested arrays)
            }

            $this->theme->upsertValue($group, $key, $value === null ? null : (string) $value);
        }

        $this->flushCache();
    }

    /**
     * Typography is nested: $data = [role => [family, weight, transform,
     * letter_spacing, size_desktop, size_tablet, size_mobile, line_height]].
     */
    public function updateTypography(array $data): void
    {
        foreach ($data as $role => $fields) {
            foreach ($fields as $prop => $value) {
                $this->theme->upsertValue('typography', "{$role}_{$prop}", $value === null ? null : (string) $value);
            }
        }

        $this->flushCache();
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CSS_CACHE_KEY);
    }

    /* ── CSS generation ───────────────────────────────────────────────── */

    /** Cached, minified-ish CSS custom-property block + component rules. */
    public function renderCss(): string
    {
        return Cache::rememberForever(self::CSS_CACHE_KEY, fn () => $this->buildCss());
    }

    private function buildCss(): string
    {
        $css = $this->localFontFacesCss();
        $css .= ':root{'.$this->colorVariables().$this->radiusVariables().$this->spacingVariables()
            .$this->typographyVariables().$this->gradients->cssVariables().$this->customVariables().'}';
        $css .= $this->gradients->utilityCss();
        $css .= $this->buttonStyles->css();
        $css .= $this->buttonCss();
        // Global Button Custom CSS (Theme → Buttons) — emitted after every
        // generated button rule so admin overrides always win the cascade.
        $css .= (string) $this->get('buttons.custom_css');
        $css .= $this->shadowCss();
        $css .= $this->typographyRoleCss();
        $css .= $this->elementTypographyCss();
        $css .= $this->responsiveSpacingCss();
        $css .= $this->layoutUtilityCss();

        if ($this->get('dark_mode.enabled', false)) {
            $css .= $this->darkModeCss();
        }

        $css .= (string) $this->get('custom_code.css_global');
        $css .= '@media (min-width: 992px){'.(string) $this->get('custom_code.css_desktop').'}';
        $css .= '@media (min-width: 768px) and (max-width: 991.98px){'.(string) $this->get('custom_code.css_tablet').'}';
        $css .= '@media (max-width: 767.98px){'.(string) $this->get('custom_code.css_mobile').'}';

        return $css;
    }

    private function colorVariables(): string
    {
        $vars = '';
        foreach (array_keys(config('theme.colors.fields')) as $key) {
            $vars .= '--gsm-'.str_replace('_', '-', $key).':'.$this->get("colors.{$key}").';';
        }

        $vars .= '--gsm-btn-gradient-from:'.$this->get('buttons.gradient_from', '#111827').';';
        $vars .= '--gsm-btn-gradient-to:'.$this->get('buttons.gradient_to', '#4b5563').';';

        return $vars;
    }

    /**
     * Admin-defined :root variables (Theme → Custom CSS/JS → Global CSS
     * Variables). One per line: `--my-var: value`.
     */
    private function customVariables(): string
    {
        $raw = (string) $this->get('custom_code.css_variables');
        $vars = '';

        foreach (preg_split('/\r?\n/', $raw) ?: [] as $line) {
            $line = trim($line, " \t;");
            if (preg_match('/^--[a-z0-9_-]+\s*:\s*[^{}<>]+$/i', $line)) {
                $vars .= $line.';';
            }
        }

        return $vars;
    }

    private function radiusVariables(): string
    {
        $vars = '';
        foreach (array_keys(config('theme.radius.fields')) as $key) {
            $vars .= '--gsm-radius-'.str_replace('_', '-', $key).':'.$this->get("radius.{$key}").'px;';
        }

        return $vars;
    }

    private function spacingVariables(): string
    {
        $vars = '';
        foreach (array_keys(config('theme.spacing.fields')) as $key) {
            $vars .= '--gsm-space-'.str_replace('_', '-', $key).':'.$this->get("spacing.{$key}").'px;';
        }

        return $vars;
    }

    /** Desktop typography values as CSS vars (tablet/mobile applied via media queries). */
    private function typographyVariables(): string
    {
        $vars = '';
        foreach (array_keys(config('theme.typography.roles')) as $role) {
            $vars .= '--gsm-font-'.str_replace('_', '-', $role).':'.$this->fontFamilyValue($role).';';
            $vars .= '--gsm-weight-'.str_replace('_', '-', $role).':'.$this->get("typography.{$role}_weight", '400').';';
            $vars .= '--gsm-size-'.str_replace('_', '-', $role).':'.$this->typographySize($role, 'desktop').'px;';
            $vars .= '--gsm-spacing-'.str_replace('_', '-', $role).':'.$this->get("typography.{$role}_letter_spacing", '0').'px;';
            $vars .= '--gsm-leading-'.str_replace('_', '-', $role).':'.$this->get("typography.{$role}_line_height", '1.5').';';
        }

        return $vars;
    }

    /** One CSS rule per typography role (text-transform can't be a bare custom property). */
    private function typographyRoleCss(): string
    {
        $css = '';
        foreach (array_keys(config('theme.typography.roles')) as $role) {
            $class = '.gsm-text-'.str_replace('_', '-', $role);
            $transform = $this->get("typography.{$role}_transform", 'none');
            $css .= "{$class}{font-family:var(--gsm-font-".str_replace('_', '-', $role).");".
                'font-weight:var(--gsm-weight-'.str_replace('_', '-', $role).');'.
                'font-size:var(--gsm-size-'.str_replace('_', '-', $role).');'.
                'letter-spacing:var(--gsm-spacing-'.str_replace('_', '-', $role).');'.
                'line-height:var(--gsm-leading-'.str_replace('_', '-', $role).');'.
                "text-transform:{$transform};}";
        }

        return $css;
    }

    /**
     * Plain elements follow the typography roles too, not only the
     * .gsm-text-* classes, so body copy no longer falls back to Bootstrap's
     * system-ui stack. Element selectors, emitted after the site stylesheet:
     * they beat Bootstrap's own element rules, and any component class still
     * beats them. Sizes only where the role names that element.
     */
    private function elementTypographyCss(): string
    {
        $roles = [
            'body' => ['body_text', true], 'h1' => ['main_heading', true], 'h2' => ['section_heading', true],
            'h3' => ['sub_heading', true], 'h4,h5,h6' => ['sub_heading', false], 'p' => ['paragraph', true],
            'button,.btn' => ['buttons', false], '.nav-link,.navbar-nav a' => ['navigation', true],
            'footer' => ['footer_text', true], 'label,.form-label' => ['form_labels', true],
            'input,select,textarea,.form-control,.form-select' => ['form_inputs', true],
            'table' => ['tables', true], '.gsm-richtext' => ['blog_content', true],
        ];

        $css = ':root{--bs-body-font-family:var(--gsm-font-body-text);--bs-body-font-weight:var(--gsm-weight-body-text);}';

        foreach ($roles as $selector => [$role, $sized]) {
            $var = str_replace('_', '-', $role);
            $css .= "{$selector}{font-family:var(--gsm-font-{$var});font-weight:var(--gsm-weight-{$var});"
                ."letter-spacing:var(--gsm-spacing-{$var});text-transform:".$this->get("typography.{$role}_transform", 'none').';'
                .($sized ? "font-size:var(--gsm-size-{$var});line-height:var(--gsm-leading-{$var});" : '').'}';
        }

        return $css;
    }

    /** Tablet/mobile font-size overrides per role. */
    private function responsiveSpacingCss(): string
    {
        $css = '@media (max-width: 991.98px){:root{';
        foreach (array_keys(config('theme.typography.roles')) as $role) {
            $css .= '--gsm-size-'.str_replace('_', '-', $role).':'.$this->typographySize($role, 'tablet').'px;';
        }
        $css .= '--gsm-space-section-padding-desktop:var(--gsm-space-section-padding-tablet);'.
            '--gsm-space-grid-gap-desktop:var(--gsm-space-grid-gap-tablet);'.
            '}}';

        $css .= '@media (max-width: 767.98px){:root{';
        foreach (array_keys(config('theme.typography.roles')) as $role) {
            $css .= '--gsm-size-'.str_replace('_', '-', $role).':'.$this->typographySize($role, 'mobile').'px;';
        }
        $css .= '--gsm-space-section-padding-desktop:var(--gsm-space-section-padding-mobile);'.
            '--gsm-space-grid-gap-desktop:var(--gsm-space-grid-gap-mobile);'.
            '}}';

        return $css;
    }

    private function typographySize(string $role, string $device): int|float
    {
        $default = (int) (config("theme.typography.roles.{$role}.default_size") ?? 16);
        $ratio = match ($device) {
            'tablet' => 0.9,
            'mobile' => 0.8,
            default => 1,
        };

        $stored = $this->get("typography.{$role}_size_{$device}");

        return $stored !== null ? (float) $stored : round($default * $ratio, 1);
    }

    /** Local upload ("local:{slug}") resolves to the uploaded family name + injects @font-face; else the Google Font name as-is. */
    private function fontFamilyValue(string $role): string
    {
        $default = config("theme.typography.roles.{$role}.default_family", 'inherit');
        $stored = $this->get("typography.{$role}_family", $default);

        if (is_string($stored) && str_starts_with($stored, 'local:')) {
            $font = ThemeFont::where('slug', substr($stored, 6))->first();

            return self::fontStack($font ? $font->name : $default);
        }

        return self::fontStack($stored ?: $default);
    }

    /**
     * A CSS font-family value from what an admin typed: each family quoted on
     * its own, generic keywords bare. Quoting the whole list — the old
     * behaviour — turned `Georgia, "Times New Roman", serif` into one font
     * name that doesn't exist. Anything that isn't part of a name is dropped,
     * so the value can't break out of the stylesheet. A list with no generic
     * keyword ends in sans-serif: until the web font arrives the browser would
     * otherwise draw its default serif, and the swap moves the text further.
     */
    public static function fontStack(string $value): string
    {
        $generic = ['serif', 'sans-serif', 'monospace', 'cursive', 'fantasy', 'system-ui', 'ui-serif',
            'ui-sans-serif', 'ui-monospace', 'ui-rounded', 'emoji', 'math', 'fangsong', 'inherit', 'initial', 'unset'];

        $families = collect(explode(',', $value))
            ->map(fn (string $family) => trim((string) preg_replace('/[^\pL\pN _-]/u', '', $family)))
            ->filter();
        if ($families->isNotEmpty() && ! $families->contains(fn (string $family) => in_array(strtolower($family), $generic, true))) {
            $families->push('sans-serif');
        }

        return $families
            ->map(fn (string $family) => in_array(strtolower($family), $generic, true) ? $family : "'{$family}'")
            ->implode(', ');
    }

    private function buttonCss(): string
    {
        $radius = $this->get('buttons.border_radius', 8);
        $px = $this->get('buttons.padding_x', 24);
        $py = $this->get('buttons.padding_y', 12);
        $shadow = $this->shadowValue($this->get('buttons.shadow', 'none'));
        $gradientFrom = $this->get('buttons.gradient_from', '#111827');
        $gradientTo = $this->get('buttons.gradient_to', '#4b5563');

        $hover = match ($this->get('buttons.hover_animation', 'brighten')) {
            'lift' => '.gsm-btn-theme:hover{transform:translateY(-2px);}',
            'scale' => '.gsm-btn-theme:hover{transform:scale(1.03);}',
            'shrink' => '.gsm-btn-theme:hover{transform:scale(0.97);}',
            'brighten' => '.gsm-btn-theme:hover{filter:brightness(1.08);}',
            default => '',
        };

        return ".gsm-btn-theme{border-radius:{$radius}px;padding:{$py}px {$px}px;box-shadow:{$shadow};".
            'transition:all .2s ease;}'.
            ".gsm-btn-theme.gsm-btn-gradient-theme{background:linear-gradient(90deg,{$gradientFrom},{$gradientTo});border:0;color:#fff;}".
            $hover;
    }

    private function shadowCss(): string
    {
        $css = '';
        foreach (array_keys(config('theme.shadows.fields')) as $key) {
            $level = $this->get("shadows.{$key}", config("theme.shadows.fields.{$key}.default"));
            $css .= '.gsm-shadow-'.str_replace('_', '-', $key).'{box-shadow:'.$this->shadowValue($level).';}';
        }

        return $css;
    }

    private function shadowValue(?string $level): string
    {
        return match ($level) {
            'sm' => '0 1px 3px rgba(0,0,0,.08)',
            'md' => '0 4px 12px rgba(0,0,0,.10)',
            'lg' => '0 10px 30px rgba(0,0,0,.14)',
            'xl' => '0 20px 50px rgba(0,0,0,.18)',
            default => 'none',
        };
    }

    /** Opt-in utility classes so pages can consume container/content width and spacing vars. */
    private function layoutUtilityCss(): string
    {
        return '.gsm-theme-container{max-width:var(--gsm-space-container-width);margin-left:auto;margin-right:auto;}'.
            '.gsm-theme-content{max-width:var(--gsm-space-content-width);margin-left:auto;margin-right:auto;}'.
            '.gsm-theme-section{padding-top:var(--gsm-space-section-padding-desktop);padding-bottom:var(--gsm-space-section-padding-desktop);}'.
            '.gsm-theme-card{margin-bottom:var(--gsm-space-card-spacing);border-radius:var(--gsm-radius-cards);background:var(--gsm-card-background);}'.
            '.gsm-theme-grid{display:grid;gap:var(--gsm-space-grid-gap-desktop);}'.
            '.gsm-theme-margin{margin-bottom:var(--gsm-space-element-margin);}';
    }

    private function darkModeCss(): string
    {
        return 'html[data-gsm-theme="dark"]{'.
            '--gsm-background:'.$this->get('dark_mode.background').';'.
            '--gsm-section-background:'.$this->get('dark_mode.section_background').';'.
            '--gsm-card-background:'.$this->get('dark_mode.card_background').';'.
            '--gsm-text-color:'.$this->get('dark_mode.text_color').';'.
            '--gsm-heading-color:'.$this->get('dark_mode.heading_color').';'.
            '--gsm-border-color:'.$this->get('dark_mode.border_color').';'.
            '}';
    }

    /**
     * One Google Fonts stylesheet per family in use. Separate links because
     * a family name is free text now: one unknown name 400s its own request
     * instead of every font on the site. Local uploads, stacks (commas) and
     * fonts every device already has are never requested.
     *
     * @return list<string>
     */
    public function googleFontsUrls(): array
    {
        $installed = ['georgia', 'times new roman', 'arial', 'helvetica', 'verdana', 'tahoma',
            'trebuchet ms', 'courier new', 'segoe ui', 'system-ui', 'inherit'];
        $families = [];

        foreach (array_keys(config('theme.typography.roles')) as $role) {
            $default = config("theme.typography.roles.{$role}.default_family");
            $family = trim((string) $this->get("typography.{$role}_family", $default));

            if ($family === '' || str_starts_with($family, 'local:') || str_contains($family, ',')
                || in_array(strtolower($family), $installed, true)) {
                continue;
            }

            $families[$family][(int) $this->get("typography.{$role}_weight", '400')] = true;
        }

        return collect($families)
            ->map(function (array $weights, string $family): string {
                ksort($weights); // Google rejects unsorted weight lists

                return 'https://fonts.googleapis.com/css2?family='.urlencode($family)
                    .':wght@'.implode(';', array_keys($weights)).'&display=swap';
            })
            ->values()
            ->all();
    }

    /** @font-face blocks for every uploaded local font. */
    public function localFontFacesCss(): string
    {
        return ThemeFont::query()->get()->map(fn (ThemeFont $font) => $font->fontFaceCss())->implode('');
    }

    /* ── Import / Export / Reset / Backup ─────────────────────────────── */

    /** Full flat snapshot: ["group.key" => ["value" => ..., "type" => ...]]. */
    public function exportPayload(): array
    {
        return $this->theme->allSettings()
            ->mapWithKeys(fn ($row) => [$row->group.'.'.$row->key => ['value' => $row->value, 'type' => $row->type]])
            ->all();
    }

    public function importPayload(array $payload): void
    {
        // An uploaded file is untrusted: keep only "group.key" => {value: scalar|null}
        // rows, so a malformed entry can't break the wipe-and-insert below.
        $payload = array_filter($payload, fn ($entry, $dotted) => is_string($dotted)
            && preg_match('/^[a-z0-9_]{1,50}\.[a-z0-9_]{1,100}$/', $dotted)
            && is_array($entry)
            && (! isset($entry['value']) || is_scalar($entry['value'])), ARRAY_FILTER_USE_BOTH);

        // Site-wide scripts are Developer Mode only: for anyone else an
        // imported file or a restored backup keeps the scripts the site has now.
        if (! Gate::allows('developer.mode')) {
            $current = $this->exportPayload();

            foreach ($this->developerKeys() as $dotted) {
                unset($payload[$dotted]);

                if (isset($current[$dotted])) {
                    $payload[$dotted] = $current[$dotted];
                }
            }
        }

        $this->theme->replaceAll($payload);
        $this->flushCache();
    }

    /** "group.key" of every field flagged 'developer' in config/theme.php. */
    private function developerKeys(): array
    {
        $keys = [];

        foreach ((array) config('theme') as $group => $schema) {
            foreach ((array) ($schema['fields'] ?? []) as $key => $field) {
                if (! empty($field['developer'])) {
                    $keys[] = "{$group}.{$key}";
                }
            }
        }

        return $keys;
    }

    /** Saves the current live state as a named, restorable snapshot (also used for "Backup Theme"). */
    public function savePreset(string $name, bool $isSystem = false): ThemePreset
    {
        return ThemePreset::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.now()->timestamp,
            'payload' => $this->exportPayload(),
            'is_system' => $isSystem,
        ]);
    }

    public function restorePreset(ThemePreset $preset): void
    {
        $this->importPayload($preset->payload);
    }

    /** Wipes all customizations — every get() falls back to config/theme.php defaults. */
    public function resetToDefault(): void
    {
        $this->theme->replaceAll([]);
        $this->flushCache();
    }

    /* ── internals ─────────────────────────────────────────────────────── */

    /** @return array<string, string|null> */
    private function cached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return $this->theme->allSettings()
                ->mapWithKeys(fn ($row) => [$row->group.'.'.$row->key => $row->value])
                ->all();
        });
    }

    private function configDefault(string $dottedKey): mixed
    {
        [$group, $key] = array_pad(explode('.', $dottedKey, 2), 2, null);

        // Typography roles: "typography.section_heading_family" → role=section_heading, prop=family
        if ($group === 'typography') {
            foreach (array_keys(config('theme.typography.roles', [])) as $role) {
                if (str_starts_with((string) $key, $role.'_')) {
                    $prop = substr((string) $key, strlen($role) + 1);
                    if ($prop === 'family') {
                        return config("theme.typography.roles.{$role}.default_family");
                    }
                    if (str_starts_with($prop, 'size_')) {
                        return null; // computed by typographySize()
                    }
                    if ($prop === 'weight') {
                        return '400';
                    }
                    if ($prop === 'line_height') {
                        return '1.5';
                    }
                    if ($prop === 'letter_spacing') {
                        return '0';
                    }
                    if ($prop === 'transform') {
                        return 'none';
                    }
                }
            }

            return null;
        }

        return config("theme.{$group}.fields.{$key}.default");
    }
}
