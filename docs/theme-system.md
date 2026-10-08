# Theme System (Theme Customizer)

Single admin screen (`/admin/theme-customizer`, permission `theme.manage`)
controlling the entire visual design of the public site. Nothing here is
hardcoded — every value has a config-declared default and a DB override.

## Storage

- `theme_settings` — flat `group`/`key`/`value`/`type` rows, unique on
  `[group, key]`. Mirrors the `settings` table pattern exactly, on purpose:
  adding a new color token or typography property is a `config/theme.php`
  change, never a migration.
- `theme_fonts` — locally uploaded font files (name, slug, woff2/woff/ttf
  paths, weight, style). Referenced from typography as `local:{slug}`.
- `theme_presets` — full JSON snapshots of `theme_settings`, used for
  Export/Import/Backup/Restore. One row (`is_system=true`, empty payload)
  is the undeletable "Factory Default" — restoring it is equivalent to
  Reset.

## `config/theme.php` — the schema

9 groups, each either a flat `fields` map (colors, buttons, spacing,
radius, shadows, animations, custom_code, dark_mode) or the special
`typography` group (`roles` map — 15 roles, each generating 8 stored
properties: `family`, `weight`, `transform`, `letter_spacing`, `line_height`,
`size_desktop`, `size_tablet`, `size_mobile`).

**Typography roles**: Section Heading, Main Heading (H1), Sub Heading, Body
Text, Paragraph, Buttons, Navigation, Footer Text, Sidebar, Form Labels,
Form Inputs, Tables, Testimonials, Blog Content, Product Content.

## `ThemeService` — how values resolve

`theme('colors.primary')` (helper) → `ThemeService::get()`:
1. Look up the DB row (`theme_settings` cache, `Cache::rememberForever('theme.all')`)
2. If no row (or null value), fall back to `config/theme.php`'s declared
   default for that key — typography keys have special parsing since their
   config default isn't a flat `fields.{key}.default`, it's derived from
   the role's `default_family`/`default_size` plus hardcoded property
   defaults (weight 400, line-height 1.5, letter-spacing 0, transform none).

## CSS generation

`theme_css()` (helper) → `ThemeService::renderCss()`, cached forever under
`theme.css` (separate key from the data cache `theme.all` — both flushed
together by `flushCache()`, called on every group/typography/font
save+delete).

The generated stylesheet, in order:
1. `@font-face` blocks for every uploaded local font (folded directly into
   this cached string — **not** a separate uncached call; an earlier bug
   had it separate and the font-face rule silently missed cache
   invalidation, caught by a test asserting the full string).
2. `:root { --gsm-* }` — colors, radius, spacing, typography (family/
   weight/size/letter-spacing/line-height per role) as CSS custom
   properties.
3. Per-role `.gsm-text-{role}` utility class (text-transform can't be a
   bare custom property, so it's baked into a real rule).
4. Tablet/mobile media-query overrides for every role's font-size (0.9×/
   0.8× ratio of the desktop default, unless an explicit per-breakpoint
   size is stored).
5. Button component CSS (`.gsm-btn-theme`, hover animation variants,
   gradient).
6. Shadow utility classes (`.gsm-shadow-{cards|buttons|dropdowns|images|navigation}`).
7. Animation timing (`.gsm-animate` transition-duration/-delay — reuses the
   Page Builder's existing scroll-animation mechanism, doesn't replace it).
8. Layout utility classes (`.gsm-theme-container`, `.gsm-theme-content`,
   `.gsm-theme-section`, `.gsm-theme-card`, `.gsm-theme-grid`,
   `.gsm-theme-margin`) — opt-in classes for future frontend modules to
   consume; existing views were **not** retrofitted onto these (see
   "Design boundary" below).
9. Dark-mode block (`html[data-gsm-theme="dark"] { ... }`) — only emitted
   when `dark_mode.enabled` is true.
10. Custom CSS, raw, per breakpoint (`custom_code.css_global` always;
    `css_desktop`/`css_tablet`/`css_mobile` wrapped in the matching media
    query).

Injected in `frontend/layouts/app.blade.php` as
`<style id="gsm-theme-vars">{!! theme_css() !!}</style>`, after the
compiled Bootstrap/SCSS `<link>` so it wins on equal-specificity cascade —
this is how theme values override the SCSS design-system defaults without
needing `!important` anywhere.

## Google Fonts + Local Fonts

`ThemeService::googleFontsUrl()` scans all 15 typography roles, dedupes
families in use, skips `local:*` references and multi-font-stack values
(commas), and builds one `fonts.googleapis.com/css2?family=...` URL loaded
via `<link rel="stylesheet">` with `preconnect` hints. Local uploads
(`theme_fonts`) get their own `@font-face` (see CSS generation step 1
above) and appear in the typography family `<select>` as `local:{slug}`.

## Design boundary — what was deliberately NOT touched

The pre-existing frontend SCSS (`resources/scss/frontend.scss`) hardcodes
brand colors (`$gsm-navy`, `$gsm-magenta`, `$gsm-orange`) used across 8+
already-tested Blade files. Rewiring all of that to Theme Customizer
variables was judged too risky for one pass — instead:
- `.gsm-btn-gradient` (the site's actual primary button) reads the new
  `--gsm-btn-gradient-from`/`-to` CSS variables as a **progressive-
  enhancement fallback** (`var(--gsm-btn-gradient-from, $gsm-magenta)`) —
  zero risk, since an unset CSS variable simply falls through to the SCSS
  value.
- New utility classes (`.gsm-text-{role}`, `.gsm-theme-container`, etc.)
  are additive and opt-in, meant for frontend modules not yet built to
  consume when they're built.

## Live Preview

`/admin/theme-customizer` has a sticky iframe (`#gsm-theme-preview`)
pointing at the homepage. Every group form (including the typography
accordion) is submitted via `fetch()` with `Accept: application/json` (so
Laravel returns 422 JSON on validation failure instead of a redirect); on
success the iframe reloads. Desktop/Tablet/Mobile buttons resize the iframe
width (100%/768px/375px) for a quick responsive check — this does not
change the CSS itself, it's the same site rendered narrower, exactly like
resizing a real browser.

Typography also has an **instant, no-reload** in-page preview: as the admin
types/selects family, weight, size, spacing, line-height, or transform, a
small JS handler applies those styles directly to a preview `<p>` element in
the accordion — no server round-trip needed for that part.
