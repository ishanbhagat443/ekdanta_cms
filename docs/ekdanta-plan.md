# Ekdanta site on Ekdanta CMS: implementation plan

> Approved on 8 Oct 2026. All paths are relative to `cms/`.

## Context

Ekdanta Branding and Marketing will build its new animated site (ekdanta.in) with this CMS. The approved design (11 desktop pages, 5 mobile pages) is almost entirely **scroll-driven**: tall sections with a pinned full-screen stage, where many parts move in sync with scroll, plus loops and interactions. Today the CMS can only fade or move a whole section once when it enters the screen, has 13 known bugs, and still carries a previous client's look.

Outcome: every page is buildable section by section in the admin panel, matching the design at 1440 × 900 and 390 × 844. All text, images, links, numbers, colours and motion are editable there (with mobile overrides). The Ekdanta work lives in a separate section pack and theme preset, so the CMS stays general. Claude in Chrome must then be able to assemble the whole site by clicking through the admin panel.

## How I will work

- **Git.** `git init` in `cms/` (the existing `.gitignore` already excludes `.env`, `vendor`, `node_modules`, `public/build` and uploads), remote `origin = git@github.com:ishanbhagat443/ekdanta_cms.git`. Baseline commit, then every phase on branch **`ekdanta-build`**, pushed after each phase. `main` is pushed only when you call it final. If the remote already has history, I stop and ask before reconciling.
- **Loop after every phase:** `php artisan test` + `npm run build` (+ `node --test` from Phase 1) → `/ponytail-review`, apply its delete-list → ui-ux-pro-max pre-delivery checklist on what changed, recorded in `docs/ekdanta-qa.md` → commit (`Co-Authored-By` trailer) → push → five-line summary. I continue to the next phase unless something needs your decision.
- **ponytail full** stays on. Each phase's route below is the result of the ladder; deferred shortcuts get `ponytail:` comments so `/ponytail-debt` can list them. Never cut: reduced motion, keyboard and screen-reader access, validation at trust boundaries, permissions, safe migrations and imports.
- **ui-ux-pro-max.** Phase 0: I hand-write `design-system/ekdanta/MASTER.md` in the skill's Master format (Global Rules, Component Specs, Style, Page Pattern, Motion, Anti-patterns, Checklist) from DESIGN-README tokens. The generator never picks colours or fonts, and never runs with `--force`. Page overrides: `pages/home.md`, `contact.md`, `services.md`, `thank-you.md`, `not-found.md` (the five drawn mobile pages). Before each Ekdanta section or admin screen: read MASTER (+ override), then targeted `search.py --domain ux` / `--stack laravel` / `--domain web|icons` searches.
- **Stop points:** plan approval (now), a non-empty remote, any new accessibility failure inside the approved design, and the final merge to `main`.

## What the code audit found (checked against the brief's audit)

| Area | Fact (file) | Consequence for the plan |
|---|---|---|
| Design files | Nested one level deeper: `docs/ekdanta-design/ekdanta-design/…` | Phase 0 flattens to `docs/ekdanta-design/` (pure move) so the brief, README and guide paths agree |
| Section types | 30 page + 7 header + 8 footer + 1 shared, listed explicitly in `SectionTypeServiceProvider`; no group concept; "Add a section" is a flat dropdown (`admin/pages/edit.blade.php:33`) | Add `group()` (default `General`) + group headers + a filter box; Ekdanta types return `Ekdanta` |
| Fields | `text, textarea, richtext, code, toggle, media, menu, select, repeater, number, color`; per-field `device` flag; media stores an id with `exists:media,id` | Add two field types only: `link` and `items` (collection picker). Media accepts an id **or** a stable key |
| Per-device data | `Section::dataFor()` = shallow `array_replace` | Motion can't live in `data_mobile` without a deep merge. See decision D1 |
| View resolution | `render-section.blade.php` includes `frontend.sections.{type}` | Add `SectionType::view()` so Ekdanta views live in `sections/ekdanta/` |
| Save path | `SectionController@update` validates `style` shape, strips dev-only keys (`withoutPrivilegedStyle`), sanitises `style.anim` | `style.motion` plugs in at the same point |
| Revisions / blocks | `PageRevisionService::snapshot` stores `data_*`, `style`, overrides, page `settings`. Global blocks propagate edits to `is_synced` instances | Motion in `style` is versioned and copied for free. The Finale can be a synced global block today |
| Header | `partials/header.blade.php` hard-codes logo + search + an all-breakpoint hamburger, and ignores every other Header Builder section | Root cause of bug 9. The header is rebuilt to render builder sections with a per-device nav style |
| Page model | `pages.settings` JSON already exists | Page settings need no migration |
| Forms | 9 field types, no steps, no hidden fields, no success actions; `Enquiry::SOURCES` is a hard-coded array; `WhatsAppService::sendInquiry` exists | Extend, don't rebuild |
| Blog | Summernote editor, no HTML purifier, `Post.is_featured` exists; `/blog` currently 301s **to** `/blogs` | Flip the redirect |
| CSP | `SecurityHeaders` is report-only, with per-directive extra hosts in `config/security.php` | Add Meta Pixel / Google Ads hosts for the thank-you tags |
| Tooling | No `animejs` npm package, no JS test runner, no Playwright; PHP GD available | Add pinned `animejs@4.5.0` (same version as today's CDN pin) and devDependencies `jsdom`, `@playwright/test`, `lighthouse` (tooling, not frontend libraries). GD slices the logo strips |

## Decisions, including where the brief can be improved

| # | The brief says | Better option | Why |
|---|---|---|---|
| D1 | Store motion in the section's per-device `data_*` | Store in **`style.motion.{desktop,tablet,mobile}`** as sparse patches over the section type's design defaults | `data_*` merges shallowly and is validated against the content schema. `style` already holds today's `anim` per device, its save path already sanitises it, and revisions, saved blocks and export copy it automatically. Same outcome, with no new merge rules |
| D2 | Scrub with `anime.onScroll` + `sync` | One passive scroll listener + rAF + IntersectionObserver gating; every track is a paused Anime.js animation that the engine `seek()`s | The design's ranges are "px since section top" and often start before it (head: −650 px). Dwell/steps need the raw progress anyway, and `onScroll`'s `sync` wiring was the root cause of last month's invisible-hero bug. Anime.js still does all the interpolation (transforms, colours, clip-path, CSS vars, springs, stagger) |
| D3 | Open WhatsApp after the brief is saved | The final button is a real `wa.me` link. Its click fires a `keepalive` save of the enquiry, then lets the tap navigate | Opening a window after an async request is blocked on iOS Safari and most popup blockers. The save is still sent first, and "Sent it? See what happens next" appears in the page |
| D4 | Nothing visible hard-coded in Blade, SCSS or JS | All text, images, links, numbers and per-item colours are fields. Illustration geometry (24 service tiles, phone mock-up, map roads, chain) stays in Blade components, and takes its colours from tokens | A field for every SVG path is unusable. The tile artwork is chosen per service with a select, as the brief already describes |
| D5 | `EkdantaSiteSeeder --with-pages` | `php artisan ekdanta:seed {--with-pages}` (one closure command that calls the seeder) | `db:seed` can't take custom options |
| D6 | Same scroll lengths everywhere | Lengths and ranges stay in **design px** in the Motion tab. The engine scales scroll by `stage height ÷ 900` (÷ 844 on mobile), and positions by `--u` | At 2560 × 1440 a fixed 3600 px section unpins before the last obstacle card finishes flipping. Scaling keeps the pacing identical per screen |
| D7 | Scale compositions fluidly | Keep the design's absolute compositions, sized in a design unit `--u = min(100vw/1440, 100svh/900)` (mobile: `/390`, `/844`), with `clamp()` floors on small text (body ≥ 13 px, captions ≥ 12 px) | Uniform scaling can't overlap or clip at any aspect ratio. The floors keep 1280 × 720 readable, and Playwright checks overlap at the extremes |
| D8 | No tablet design | **768–991 px uses the mobile composition**, centred and scaled by `--u` (capped at 1.35×). ≥ 992 px uses desktop | Portrait tablets read like a large phone, and nothing overlaps because the scaling is uniform |
| D9 | Pinned stages never trap focus | Keyboard focus on a part that is still hidden scrolls its scene to the progress where that part is fully shown | Without this, Tab lands on invisible links inside a pinned stage |
| D10 | Hide the Dealer, Distributor and Career forms | `is_active = false`, and the form pickers list active forms only. Forms and submissions are untouched; an "Inactive" filter on the Forms list | "Hidden" without data loss |

## Accessibility decisions (approved by you, 8 Oct 2026)

Contrast was measured on the design tokens before building. You approved these fixes; the rest of the design stays as drawn. They go into MASTER.md and the Ekdanta theme preset, and are logged in `docs/ekdanta-qa.md`.

| Item | Measured | Approved fix | Result |
|---|---|---|---|
| Focus ring `#F2A230` | 2.1:1 on white, 1.3:1 on green | Keep the 3 px orange outline, add a 2 px ink `#0D1410` ring outside it | Ink ring 18.7:1 on white, 6.9:1 on green |
| Muted captions `#7B867F` on paper (small text) | 3.5:1 | `#6A736D` where text is small | 4.5:1 |
| Green eyebrows `#1E8E47` under 18 px | 3.9:1 | `#1B8141` for small text; large green headings keep `#1E8E47` | 4.53:1 |
| "/6" suffix `#9AA39E` (64 px) | 2.4:1 | `#85908A` | 3.04:1 |
| White "BIG BRAND!" on `#2EB45D` (200 px) | 2.7:1 | Finale band `#2CAA58`; its footer line `#0E3A22 → #0D351F`. Buttons elsewhere keep `#2EB45D` | 3.0:1 and 4.53:1 |

Not failures: unlit founder-story words (1.5:1) are a transient scroll state that ends fully lit, and are shown lit under reduced motion. The mobile menu arrows (2.9:1) are decorative (`aria-hidden`).

## Data models

### Motion (`style.motion`, Phase 3)

```jsonc
"motion": {
  "desktop": {                       // sparse patch over the type's design defaults
    "off": false,                    // switch all motion off on this device
    "stage":  { "pin": true, "length": 3600 },                    // design px
    "groups": { "tile-1-3": { "range": [1690, 2020] } },          // sub-timelines
    "steps":  { "part": "cards", "count": 6, "range": [0, 2700], "dwell": 0.4 },
    "scene":  { "params": { "openSeconds": 10, "scrollOpen": 600 } }, // typed, per section type
    "tracks": {
      "head": { "part": "head", "trigger": "scrub", "preset": "head-rise",
                "params": { "distance": 40 }, "range": [-650, -230], "ease": "outCubic",
                "stagger": 0, "reduce": "final", "enabled": true },
      "flip": { "part": "cards", "children": true, "trigger": "scrub", "preset": "flip-y",
                "range": [0, 300], "stagger": 420, "ease": "inOutQuad" }
    }
  },
  "tablet": { },                     // tablet/mobile patches; "inherit": true copies desktop
  "mobile": { "tracks": { "head": { "params": { "distance": 30 } } } }
}
```

- **Defaults** live in one JSON file per Ekdanta section, `resources/motion/ekdanta/<section>.json` (`desktop` + `mobile`, transcribed from `renderVals()`). The PHP type reads it for the Motion tab, the page reads the merged result. "Reset to design default" deletes the patch.
- **Track:** part (`data-m` name, `@self`, optional `children`), trigger (`scrub | enter | load | loop | hover | click | state`), `preset` (Motion Library slug) **or** `keyframes` (JSON, validated), `params` (placeholder values such as `{distance}`), duration, delay, ease (named, `steps(n)` or `spring(…)` → `createSpring`), stagger (ms, or scroll px for scrub), range (`px` from the section top, or `p` 0–1 inside a group), `step` (for `state`), `reduce` (`final | hide`), `enabled`.
- **Validation:** `App\Support\SectionMotion::sanitise()` (successor of `SectionAnimation::sanitiseAll`). It whitelists properties and units, clamps numbers, checks that presets exist, accepts keyframes only as data (no functions, no `url()`). A track's custom JS and scene code need `developer.mode`.
- **Output:** a `<script type="application/json" data-ek-motion>` inside the section wrapper, with all three devices merged. The engine picks the device by width.

### Collections (Phase 4.1)

The smallest design that still gives typed, validated forms is **one table plus typed PHP collection classes that reuse the section field engine**:

- `collection_items`: `id, collection, slug, title, status (draft|published), sort_order, data JSON, timestamps, soft deletes`; unique `(collection, slug)`. SEO uses the existing `HasSeoMeta` morph.
- `App\Collections\{CaseStudies, ServiceGroups, Services, Clients, Reels, Faqs, TeamRoles}` each declare `fields()` in the same format as `SectionType`. Validation (`rules()`), the admin form (`admin/builder/partials/field`) and the repeater all come for free.
- One `CollectionItemController` + one set of views (index with drag reorder using the admin's existing sortable, create/edit, delete), permission **`collections.manage`**.
- Relations are slug fields: a service's group, a case study's "next case", related work, related services, FAQ group (page or service slug), client industry.
- Seven tables, models and controllers would add ~40 files for the same forms. The tradeoff is that you can't query JSON columns cheaply, which these list sizes (≤ 50 items) never need.

### Templates (Phase 4.2)

- Page setting `template_for ∈ {case_study, service, post}`. Routes: `/work/{slug}`, `/services/{slug}` (services with a detail page), `/blog/{slug}`. Each renders the published template page with the item shared to every section as `$item`.
- Fallbacks keep other sites working: no template page → the existing blog views. `/blog` serves the published page with slug `blog` if there is one, otherwise the existing listing. `/blogs` and `/blogs/{slug}` 301 to `/blog…`.
- The builder preview of a template page gets an **item picker** (`?preview=1&item=<slug>`, drafts included).

### Settings and page roles (Phase 2)

- `business.phones` `[{label, number, whatsapp}]`, `business.offices` `[{name, address, directions_url, lat, lng, hours}]`, `business.reply_time`. These are JSON values in the existing settings store, edited with the builder's repeater partial. Old single `phone/whatsapp/address` values migrate in, and the migration is reversible.
- Special roles are settings `pages.home_id`, `pages.not_found_id` and `pages.thank_you_id`, set from page settings ("Use this page as …"), so only one page can hold each role. A role takes effect publicly only once its page is published; preview always works. Home falls back to slug `home`.

## Section types (53, admin names exactly as briefed)

Key = `ekdanta-<slug>`. Class: `App\Services\SectionTypes\Ekdanta\<Name>Section`. View: `frontend/sections/ekdanta/<slug>`. Anchor field defaults to the design id.

| Page | Admin name → key (anchor) |
|---|---|
| Home | Ekdanta · Home Hero (Torch) → `home-hero` (`j-top`) · Ekdanta · Obstacles (Flip Cards) → `obstacles` (`j-obstacles`) · Ekdanta · Work Showcase → `work-showcase` (`j-work`) · Ekdanta · Reels Carousel → `reels-carousel` (`j-reels`) · Ekdanta · Brand Shelves → `brand-shelves` (`j-brands`) · Ekdanta · Services Panels → `services-panels` (`j-services`) · Ekdanta · Founders Story → `founders-story` (`j-about`) · Ekdanta · Big Brand Finale → `finale` (**`contact`**) |
| Contact | Ekdanta · Contact Hero (Phone Dial) → `contact-hero` (`c-top`) · Ekdanta · Contact Channels → `contact-channels` (`c-channels`) · Ekdanta · Brief Builder → `brief-builder` (`c-brief`) · Ekdanta · Visit Us (Map) → `visit-us` (`c-visit`) · Ekdanta · FAQ Deck → `faq-deck` (`c-faq`) · Finale |
| Services | Ekdanta · Services Hero (Orbit) → `services-hero` (`s-top`) · Ekdanta · Expertise Cards → `expertise-cards` (`s-menu`) · Ekdanta · Service Deep Dive → `service-deep-dive` (`s-deep`) · Ekdanta · Process Path → `process-path` (`s-process`) · Ekdanta · Proof Carousel → `proof-carousel` (`s-work`) · Ekdanta · Pricing (No Fixed Packages) → `pricing` (`s-price`) · Finale |
| Thank you | Ekdanta · Thank-you Hero → `thank-you-hero` (`t-top`) · Ekdanta · Next Steps Track → `next-steps` (`t-next`) · Ekdanta · Save Our Number → `save-our-number` (`t-save`) · Ekdanta · While You Wait → `while-you-wait` (`t-wait`) · Finale |
| 404 | Ekdanta · 404 Hero → `not-found-hero` (`n-top`) · Ekdanta · Page Finder → `page-finder` (`n-routes`) · Ekdanta · Report Broken Link → `report-link` (`n-report`) · Finale |
| About | Ekdanta · About Hero (Dictionary) → `about-hero` (`a-top`) · Ekdanta · Our Story → `our-story` (`a-story`) · Ekdanta · Four Promises → `four-promises` (`a-values`) · Ekdanta · Team Roll Call → `team-roll-call` (`a-team`) · Ekdanta · Who We Work With → `who-we-work-with` (`a-brands`) · Finale |
| Work | Ekdanta · Work Hero (Wall) → `work-hero` (`w-top`) · Ekdanta · Case Studies Scroller → `case-studies-scroller` (`w-cases`) · Ekdanta · Reels Arc → `reels-arc` (`w-reels`) · Ekdanta · Client Logo Wall → `client-logo-wall` (`w-clients`) · Finale |
| Case study template | Ekdanta · Case Hero → `case-hero` (`c-top`) · Ekdanta · Case Overview → `case-overview` (`c-overview`) · Ekdanta · Case Gallery Zoom → `case-gallery` (`c-gallery`) · Ekdanta · Case Results → `case-results` (`c-result`) · Ekdanta · Next Case → `next-case` (`c-next`) · Finale |
| Service detail template | Ekdanta · Service Hero (Live Scene) → `service-hero` (`v-top`) · Ekdanta · What's Included → `whats-included` (`v-incl`) · Ekdanta · How It Works → `how-it-works` (`v-steps`) · Ekdanta · Related Work → `related-work` (`v-proof`) · Ekdanta · Service Pricing + FAQ → `service-pricing-faq` (`v-price`) · Ekdanta · Works Well With → `works-well-with` (`v-related`) · Finale |
| Blog listing | Ekdanta · Blog Hero (Notebook) → `blog-hero` (`b-top`) · Ekdanta · Featured Article → `featured-article` (`b-feat`) · Ekdanta · Article Grid → `article-grid` (`b-posts`) · Ekdanta · WhatsApp Tips → `whatsapp-tips` (`b-sub`) · Finale |
| Blog article template | Ekdanta · Article Hero (Typewriter) → `article-hero` (`p-top`) · Ekdanta · Article Body + Reading Rail → `article-body` (`p-body`) · Ekdanta · Keep Reading → `keep-reading` (`p-more`) · Finale |

## Phases

### Phase 0: Setup
Set up git as above, flatten the design folder, copy this plan to `docs/ekdanta-plan.md`, write MASTER.md + 5 page overrides, `npm i -E animejs@4.5.0`.
**Smallest route:** nothing to build; files and config only.

### Phase 1: The 13 bugs (one minimal diff each, regression test where it makes sense)

| # | Root cause found | Fix | Test |
|---|---|---|---|
| 1 Home preview 404 | Catch-all regex excludes `home`; `home()` has no preview branch; three views hand-build `url('/'.$slug).'?preview=1'` | `Page::previewUrl()` (home → `/?preview=1`) used by all three; `/` honours `?preview=1` for `pages.manage`; `Route::permanentRedirect('/home','/')` | Feature |
| 2 404 prints Blade | The apostrophe in "doesn't" ends the inline `@section('message', '…')` | Block form; link to `/blog` | Feature |
| 3 Springy is linear | `EASES.spring = 'spring(1, 80, 10, 0)'` is v3 syntax | `anime.createSpring({ stiffness: 80, damping: 10 })` | `node --test` + jsdom: spring ≠ linear at 50 % |
| 4 Two animation lists | `config/theme.php` `animations.types` is a separate list; `default_type` is read nowhere | Types come from `SectionAnimation::PRESETS`; `default_type` becomes the preset a new section starts with (Phase 3 moves the source to the Motion Library) | Feature |
| 5 Stack quoted as one name | `ThemeService::fontFamilyValue()` returns `"'{$stored}'"` | Quote each family, leave generics bare, strip `;{}<>` | Unit |
| 6 Two font sources | Brand reads `typography.{role}.family`; ThemeService stores `{role}_family`; the select only lists config fonts, so unlisted values save as option 1 | Brand reads through ThemeService. The family select becomes a text input + native `<datalist>` (Google list + uploaded fonts), so any Google Font or `local:` font can be used | Feature |
| 7 Body font not applied | `renderCss()` emits only `.gsm-text-*` classes and `:root` vars | Map elements to roles (`body`, `h1`–`h6`, `p`, buttons, nav, footer, labels, inputs, tables, rich text) and set `--bs-body-font-family` | Feature |
| 8 Blackletter footer | `frontend.scss:1623` fallback stack | Point it to the section-heading font | n/a (CSS) |
| 9 No desktop nav | Header hard-codes a hamburger at every width | Header Builder setting `nav_style` per device (`inline | dropdown | overlay`); the header renders builder sections in order (search only when its section is added) | Feature |
| 10 Active item unreadable | Bootstrap's blue `.dropdown-item.active` under gold text | Theme CSS sets the active colour pair (checked ≥ 4.5:1) | Feature |
| 11 Lopsided buttons | **Seeded data**: corners 999/12/12/999 px + red `#F03225` glow ("rounded left edge", 02 Hero.png) | Guarded data migration that updates only rows still equal to the seed, plus the same fix in the seeder | Feature |
| 12 Leftovers | Navy/gold defaults in `config/theme.php` + ~60 SCSS fallbacks; Luxury and magenta gradients; "garland" labels; two client-named section labels; "Get Price"; `config('gsm.name')` fallback in the header and titles; `Enquiry::SOURCES` | Neutral defaults (one SCSS variable for the fallbacks); guarded data migrations for gradients and buttons; neutral labels (keys and types kept); site name with no product fallback; enquiry source = the form (stored strings kept and shown with the form's name); Dealer/Distributor/Career → D10 | Feature |
| 13 Script permissions | Header/Footer Builder `custom_js` and Theme Customizer `custom_code.js_*` save with only `builder.*` / `theme.manage` | Strip those keys without `developer.mode`, the same pattern as `SectionController::withoutPrivilegedStyle`, and hide the fields. Also verify that SEO `schema_json` can't break out of `</script>` | Feature, per path |

**Smallest route:** a minimal diff per bug, no refactors beyond the fix. Bug 9's header rewrite is the one larger diff, and Phase 2 needs it anyway.

### Phase 2: Site settings, theme and page settings
- Multi-phone and office settings + reply time (model above); helpers `business_phones()` / `business_offices()` feed the closing card, contact channels, vCard, mobile menu call line and footer.
- vCard: `GET /contact.vcf`, a string built in PHP from the settings.
- **Ekdanta theme preset:** a generic `tokens` group in Theme Customizer (named colours, radii, shadows, focus ring → CSS custom properties `--tk-*`), editable in Brand (colours) and Theme Customizer (all). The preset ships as `database/presets/ekdanta-theme.json`, imported through the existing Theme Customizer import and also registered as a `ThemePreset`. Each install has its own database, so applying it affects only this site. Fonts: Poppins + Hind through the existing Google Fonts URL builder. The css2 API serves the Devanagari subset for both by `unicode-range`; verified in the browser.
- **Page settings** (`pages.settings`): header variant (normal / over the hero / floating pill / hidden), header menu, CTA label + link (defaults in Header Builder), footer on/off (automatically off when a section type declares `hidesFooter()`, which the Finale does), page CSS/JS + head/body codes (`developer.mode`), "Use this page as" home / 404 / thank-you, template role.
- **404:** `Handler::render` renders the marked page with status 404 and keeps `NotFoundLog`. **Sitemap** excludes role pages and template pages.
- **Builder preview:** presets 1440 × 900, 1280 × 720, 390 × 844, 360 × 640 as a fixed-size iframe scaled to fit the pane (the page sees the real viewport). A Reduce motion toggle adds `reduce-motion=1`.

**Smallest route:** as briefed. JSON in the settings store, the preset through the existing import, page settings in the existing JSON column, presets as an iframe size switch.

### Phase 3: Motion system v2 (the core)
**3.1 Engine** in `resources/js/frontend/motion/` (bundled by Vite, `animejs@4.5.0` pinned):
- `index.js`: boot, reduce detection (OS query, site setting `animations.reduce_motion`, `?reduce-motion=1`), public API `EkMotion.register(type, factory)`, `.get(el) → {progress, px, step}`, `.refresh()`, `.scrollToProgress(el, p)`, and the legacy `data-anim` runtime ported from today's fixed `gsm-animations.js` (the CDN tag and `gsm.anime` config are removed).
- `scene.js`: per-section progress (D2), pin via `position: sticky`, section height = length × scale, section tops cached on load/resize (no layout reads in the frame loop), off-screen scenes skipped and their loops paused.
- `tracks.js`: builds paused Anime.js animations from track config. `scrub` → `seek()`; `enter` → IntersectionObserver play with the 1 s failsafe kept; `load`; `loop` (Anime loop, or the design's CSS `@keyframes` toggled by class); `hover`/`click` (also on focus/Enter); `state` (played when its step becomes active).
- `behaviours.js`: steps with `snap()` dwell (`is-active`, `aria-current`, index), count-up (`Intl.NumberFormat('en-IN')`), text split (Anime's splitter, with the original text kept as an `aria-label`), path draw (`pathLength=1` + dash offset), clip-path circle/polygon wipes and CSS-variable tracks, colour interpolation, word roller, marquee, orbit, deck/fan/flip, horizontal track. Almost all of these are Motion Library presets, not code.
- **Two preset kinds, one JSON format each.** The design's time-based motion is CSS: 146 named `@keyframes` (149 distinct bodies; 34 on Contact alone for the phone dial), while its scroll motion is `renderVals()` math.
  - `css` presets (triggers `load`, `loop`): keyframes JSON is a percent → declarations map. The server compiles only the presets used on a page into `@keyframes ek-<slug>`; a track applies them through CSS variables (name, duration, delay, ease, iterations, direction), so on/off, swap and timing are editable. Off-screen scenes get `animation-play-state: paused`; reduced motion → `animation: none`. The torch's `@property --ax/--ay/--mx/--my` registrations stay in SCSS.
  - `anime` presets (triggers `scrub`, `enter`, `state`, `hover`, `click`): Anime.js property keyframes with `{param}` placeholders.
  - Seed data: a small parser (kept in `database/data/` for re-runs) converts the 149 design keyframe bodies into library presets (namespaced slugs; mobile variants become params or `-m` presets). The scroll presets (head rise, flip, fan, deal, stamp, tumble, pop, draw, wipe, count-up, word highlight, typewriter, marquee, orbit, word roller) are written by hand, plus today's 9 entrance presets.
- `resources/js/frontend/sections/ekdanta/*.js`: **five** scene factories, the only places where keyframes can't express the math, all with typed params in the Motion tab: `torch-beam` (Home hero: time-or-scroll open), `work-intro` (Home work: scatter → hero, then hands over to the carousel), `index-carousel` (items placed by distance from a continuous index: Home reels, Home work cases, Services proof, Work reels arc), `scatter-to-slots` (seeded scatter → pop → arc flight onto shelves: Home brands, Work client cascade), `card-deck` (Contact FAQ throw-away deck). Everything else on the 11 pages maps to tracks, groups, steps and behaviours.
- Performance: transform/opacity/clip-path/CSS vars. Width bars become `scaleX`, colour changes become cross-faded layers where identical; otherwise the design's property stays, on small elements only.
- Accessibility: reduced motion → final states, steps switch without tweening, loops off. No JS → `html:not(.js-enabled)` flattens stages into normal flow so all text is readable. D9 focus handling. Text stays real, selectable DOM text.

**3.2 Admin:**
- **Motion tab** beside Visibility, with its own form posting `style.motion` and device tabs: stage (pin + length), track table (part, trigger, preset or custom keyframes, timing, range, ease, stagger, enabled, device override), groups, typed scene params, Reset per track and per section. Preview: a scroll-position slider driving the live preview iframe through `postMessage → EkMotion.scrollToProgress`, plus a play button for time-based tracks.
- **Motion Library** under Design, copying the Gradient Library pattern (model `MotionPreset`, migration, repository + interface, service, controller, views, routes): create, edit, duplicate, preview, export/import JSON. Ships the 9 entrance presets plus every preset the design uses. Permission **`motion.manage`**; section motion follows `pages.manage`; custom JS needs `developer.mode`.
- Migrations (reversible, flagged): `style.anim` → one `enter` track; Hero `entrance` → a hero content track (the field is then removed from the form); Theme Customizer animation types read the library's entrance presets.

**Smallest route:** Anime.js does interpolation, springs, stagger, colours and splitting. Native sticky + scroll + IntersectionObserver supply progress. My code is glue: config reading, steps/dwell, `scrollToProgress`. No new table for per-section motion.

### Phase 4: Collections, templates, section pack, forms, blog
- **4.1 Collections** as modelled above, seeded from the designs.
- **4.2 Templates** as modelled above, plus SEO fields on items.
- **4.3 Section pack.** First, import `docs/ekdanta-design/assets/` into the Media Library with stable keys (`media.key`, unique nullable). GD slices the two logo strips into 44 logo files, each with its industry. Shared base `EkdantaSection` (group, view path, anchor field, motion defaults, mobile overrides, `hidesFooter`) and Blade components (eyebrow + heading reveal, button, phone mock-up, card), so each type is mostly a field schema + view + SCSS (`resources/scss/ekdanta/`, a separate Vite entry loaded only on pages with Ekdanta sections). Defaults are the exact design copy; `[…]` placeholders stay as field values. The **Finale** is a synced global block inserted with "Add a saved block". The **header** is configured in the Header Builder (floating pill, inline nav on desktop, WhatsApp + overlay menu on mobile, progress bar, per-page CTA), with no Ekdanta-only code. Build order: base + components + Finale + header → Home → Contact → Services → Thank-you → 404 (final, with mobile designs) → About → Work → Case study → Service detail → Blog → Blog article (mobile derived from the five drawn mobile pages).
- **Interactions** (every handler in the design files): tap-to-jump via `EkMotion.scrollToProgress` (Contact channels and FAQ dots/arrows, Services expertise cards and deep-dive tabs, Work case index, About promises, Service-detail step tabs; related services and case-study next/previous are real links); the Brief Builder steps (4.4); 404 search over one small JSON list of pages, services and posts filtered in the browser; Blog topic chips + search and the Work industry filter, both reordering existing DOM nodes; Service-detail FAQ accordion (native `<details>`); copy link + share; the mobile menu; the thank-you vCard download and the visitor's local "Received today at …" time.
- **4.4 Forms.** `forms.settings` and `form_fields.settings` JSON (one migration). Adds steps, chips display for radio/checkbox, a `hidden` type with autofill (page URL, UTM tags, referrer), a per-field WhatsApp sentence (`Business type: {value}.`), success actions (message / page / WhatsApp + then offer a page), a "store as enquiry" flag, and the enquiry source = the form. D3 flow. The seeder creates "Ekdanta Brief", and the Brief Builder's form picker defaults to it.
- **4.5 Blog.** Topic chips from categories, a reading-time accessor, a table of contents from the H2s (server side, ids added), the minutes-left ring + share buttons in the engine. **In-article blocks:** a Summernote "Insert block" menu with 6 HTML snippets (buyer-journey strip, compare rows, wide figure, pull quote, reel ideas, checklist), each a set of class hooks that the editor then edits in place, styled and animated by the article template.

**Smallest route:** reuse the field engine, the existing global blocks, Header Builder and form builder; one new table, two new field types.

### Phase 5: Seed data, import/export, admin guide
- `EkdantaSiteSeeder` (idempotent, `updateOrCreate` on keys and slugs, touches nothing else): media with keys, collections, theme preset, business settings, "Home page menu" + "Main menu" (Blog in the footer), header, Finale global block, "Ekdanta Brief" form, design articles as **draft** "SAMPLE – …" posts. No pages unless `php artisan ekdanta:seed --with-pages` (D5), which runs on a separate database (`.env.preview`, `ekdanta_cms_preview`). `.env.preview` is added to `.gitignore` first: the existing `.env` rule matches only that exact filename, so the preview credentials would otherwise be pushed.
- Export/import JSON for pages, saved blocks and Motion presets. Media is referenced by key and items by slug, with an optional bundle of files and items. Imports go through the same validation, motion sanitiser and `developer.mode` gate as the editor.
- Guide `docs/ekdanta-admin-guide.md` rendered at `/cms/help/ekdanta` with `Str::markdown(…, ['html_input' => 'escape'])`.

**Smallest route:** as briefed.

### Phase 6: Verify
Feature tests (every Ekdanta section renders with defaults, field edits change output, mobile overrides apply only on mobile, motion JSON validation, 404 status, the brief creates an enquiry and returns the WhatsApp link, vCard numbers, `/home` and `/blogs` redirects, custom-code permissions). Playwright on the preview database: every page at 1440 × 900 and 390 × 844, at each section's start, ⅓, ⅔ and end, compared against the design files and fixed; again with `reduce-motion=1`; overlap checks at 1280 × 720 and 360 × 640; no console errors; CLS < 0.1; JS-off readability. Lighthouse mobile ≥ 85 on Home. Pre-delivery checklist per page in `docs/ekdanta-qa.md`. `/ponytail-audit` + `/ponytail-debt`.

## Verification commands
```
php artisan test
npm run test:js
npm run build
php artisan ekdanta:seed --with-pages --env=preview
npx playwright test tests/visual
npx lighthouse http://127.0.0.1:8001/ --form-factor=mobile --only-categories=performance
```

## Critical files
- Engine and section core: `app/Services/SectionTypes/SectionType.php`, `app/Providers/SectionTypeServiceProvider.php`, `resources/views/frontend/partials/{section-wrapper,render-section,header}.blade.php`, `app/Http/Controllers/Admin/SectionController.php`, `app/Support/SectionAnimation.php` → `SectionMotion.php`, `resources/views/admin/builder/partials/section-card.blade.php`, `public/js/gsm-animations.js` (ported, then removed), `resources/js/frontend/app.js`, `vite.config.js`.
- Theme and fonts: `app/Services/ThemeService.php`, `config/theme.php`, `app/Http/Controllers/Admin/BrandController.php`, `resources/views/admin/theme/partials/typography.blade.php`.
- Routing and pages: `routes/web.php`, `app/Http/Controllers/Frontend/PageController.php`, `app/Services/PageRenderService.php`, `app/Exceptions/Handler.php`, `resources/views/errors/404.blade.php`, `app/Services/SeoService.php`.
- Patterns to copy: Gradient Library (`GradientController`, `GradientService`, `GradientRepository(+Interface)`, `admin/gradients/*`), `GlobalBlockService` (synced blocks), `PageRevisionService`, `ThemeService::{exportPayload, importPayload, savePreset}`.
- Forms: `app/Models/{Form,FormField,Enquiry}.php`, `app/Services/{FormSubmissionService,EnquiryService,WhatsAppService}.php`.
