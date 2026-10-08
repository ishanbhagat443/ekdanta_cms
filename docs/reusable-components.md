# Reusable Components

## Section Type Registry — the core reusability mechanism

Every block that can appear in the header, footer, or a page is a
`SectionType` class registered in
`app/Providers/SectionTypeServiceProvider.php`, in this order:

**Header** (7): Logo, TopBar, AnnouncementBar, NavMenu, CtaButton, Search,
LanguageSwitcher
**Shared** (1, usable in header/footer/page): CustomHtml
**Footer** (8): FooterAbout, FooterLinks, FooterContact, Newsletter,
SocialIcons, PaymentIcons, Copyright, FooterForm
**Page** (19): Hero, Banner, Cta, Faq, Timeline, Gallery, Team, Counter,
Features, Testimonials, Video, Map, Accordion, Tabs, Cards, ImageBlock,
TextBlock, Slider, Form

Each `SectionType` declares:
- `key()` — matches the Blade file `resources/views/frontend/sections/{key}.blade.php`
- `label()`, `icon()` — shown in the admin "Add Section" picker
- `allowedAreas()` — which of `header`/`footer`/`page` can use it
- `fields()` — the admin form schema for this block (same `type`-driven
  pattern as Settings/Theme: `text`, `textarea`, `select`, `media`, `menu`,
  `repeater`, `toggle`, `code`)

**To add a new block type**: create one PHP class + one matching Blade
file, register it in the service provider array. No migration, no
controller change — `sections` is already a generic polymorphic table.

## Global Block Library — reusable named instances, not new block types

The Block Library (`/admin/global-blocks`, `blocks.manage`) is a separate
layer that sits ON TOP of the Section Type Registry above — it does not add
new block types or a second rendering engine. A `GlobalBlock` is a named,
versioned, categorizable **template** for one existing `SectionType`'s
payload (any of the 35 types above). The admin workflow:

1. **Create** a block: pick a name + an existing `SectionType` (Hero, FAQ,
   Testimonials, whatever). This creates a `GlobalBlock` row with that
   type's default payload.
2. **Edit its content**: the edit screen renders the exact same
   `admin/builder/partials/field.blade.php` field partial the Page/Header/
   Footer Builder use — because `GlobalBlock` implements `dataFor($device)`
   and `styleValue($key)` with the identical signature `Section` has. No
   field-type code was duplicated.
3. **Place it on a page**: "Add Existing Block" in the Page Builder creates
   a `Section` row linked to the master (`global_block_id` set,
   `is_synced=true`) carrying a copy of the master's current payload.
4. **Edit the master later**: choosing "Update all pages" (the default)
   pushes the new payload to every synced instance and flushes their page/
   layout render caches in the same request. Unchecking it saves the
   master only — instances keep their last-synced content until the next
   propagated save.
5. **Detach**: any linked instance can be frozen to a local copy
   (`is_synced=false`) from its section card — the master keeps evolving
   independently, the page's copy stops receiving updates but the link is
   remembered (shows as "used on N pages, 1 detached" in the library).
6. **Promote**: the reverse direction — any existing local section can
   become a brand-new master via the cubes icon on its card, turning
   "I built this once, I want it everywhere" into a one-click action
   instead of manual copy-paste.

Preview (`admin.global-blocks.preview` route) renders through
`frontend.partials.section-wrapper` — the SAME Blade a real page uses —
via a transient, never-persisted `Section` instance built from the block's
payload, so the preview is pixel-identical to production, not a
re-implementation of the section's markup.

Deleting a master **never deletes page content** — every linked instance
survives as an ordinary local `Section` with whatever payload it last had;
only the link is cleared.

## Device-responsive sections

Every section stores `data_desktop` (full payload) plus `data_tablet` /
`data_mobile` (sparse override diffs, merged over desktop at render time via
`Section::dataFor($device)`). Per-device visibility and animation live in
the `style` JSON column, rendered via `Section::visibilityClasses()`
(`d-none d-md-block d-lg-block`-style Bootstrap breakpoint classes — chosen
over a JS show/hide approach so visibility is SEO-safe and doesn't flash).

## Frontend Blade partials (`resources/views/frontend/partials/`)

Shared fragments used across many sections/pages, not tied to one
`SectionType`:

- `header.blade.php`, `topbar.blade.php`, `footer.blade.php` — the three
  Layout areas, rendered by a view composer that calls
  `LayoutService::renderPayload()` (cached).
- `section-wrapper.blade.php` — wraps every section with its style
  (background, padding, margin, animation class) before including the
  section's own Blade file.
- `section-heading.blade.php`, `device-text.blade.php` — small reusable
  fragments most Page section types share (heading block, per-device text
  span).
- `menu-item-link.blade.php` — renders one `<a>` for a `MenuItem`: label,
  icon, badge, CSS class, ID attribute, active-state — used by nav-menu,
  the mobile drawer, and footer-links so there's exactly one place that
  knows how to render a menu link.
- `mega-menu-column.blade.php` — switches on `MenuItem::column_type`
  (links/image/custom HTML/CTA) — the mega-menu content renderer.
- `seo-head.blade.php` — generic SEO meta block (title, description,
  keywords, canonical, robots, OG, Twitter Card, JSON-LD) — every
  page/post that needs SEO tags builds a `$seo` array and includes
  this partial; the block itself has no per-module knowledge.
- `dynamic-form.blade.php` — renders any Form Builder form from its field
  definitions (all 9 field types, validation errors, honeypot, success
  flash) — used by the page-builder Form section and directly by the
  contact page.
- `page-title-banner.blade.php`, `buttons.blade.php` — small design-system
  fragments (inner-page banner, CTA button repeater renderer).

## Admin-side reusable partials

- `admin/partials/seo-fields.blade.php` — the SEO fieldset used identically
  on Post and Page edit screens (backed by the `HasSeoMeta` trait's
  `seoRules()`).
- `admin/theme/partials/field.blade.php` /  `group-form.blade.php` — the
  Theme Customizer's generic type-driven field renderer, reused across
  every non-typography tab (colors/buttons/spacing/radius/shadows/
  animations/custom_code/dark_mode) — adding a field to `config/theme.php`
  is enough, this partial handles rendering it.

## JavaScript components (`public/js/admin/`)

| File | Purpose |
|---|---|
| `media-library.js` | Media Library grid/browser (drag-drop upload, folder tree, search/filter) |
| `media-picker.js` | Full media-picker modal component (`.gsm-media-picker`, postMessage iframe pattern) |
| `media-picker-field.js` | Lightweight single-field picker (`.gsm-bf-media-pick` buttons) used inside builder forms |
| `header-builder.js` | Shared drag-drop + AJAX save logic for Header AND Footer Builder (`#gsm-live-preview` reload pattern) |
| `post-form.js` | Blog post create/edit form behavior |
| `form-builder.js` | Form Builder field-repeater editor |
| `menu-editor.js` | Menu Builder: nested drag-drop tree (SortableJS) + full item-field AJAX CRUD + reference-search combobox |
| `theme-customizer.js` | Theme Customizer: AJAX per-tab save, live-preview iframe reload, responsive-width toggle, live typography preview |

**Live-preview pattern**: Header Builder, Footer Builder, Page Builder, and
the Theme Customizer all use the same convention — an `<iframe id="gsm-live-preview">`
(or `#gsm-theme-preview`) that reloads after every successful AJAX save.
This was chosen over no-reload live-CSS-injection for simplicity and
robustness; it satisfies "live preview" without a second rendering pipeline
to keep in sync with the real one.

## Scroll animations (frontend)

`.gsm-animate` + `.gsm-anim-{fade-in|fade-up|zoom-in|slide|bounce}` classes,
`IntersectionObserver`-driven in `resources/js/frontend/app.js` (adds
`.gsm-animated` once visible). Duration/delay are Theme-Customizer-driven
CSS variables (`--gsm-animate` transition timing); the mechanism itself
predates the Theme Customizer (built in the Page Builder pass) and was
deliberately extended rather than replaced.
