# Admin Modules

Every screen below lives under `/admin`, requires an active
(`is_active=true`) authenticated user, and is gated by a specific Spatie
permission (`can:{permission}` middleware in the controller constructor).
Super Admin bypasses every gate via `Gate::before` — permission rows for
Super Admin are intentionally absent from the seeder.

| Module | Route prefix | Permission | Controller |
|---|---|---|---|
| Dashboard | `admin` | (any authenticated + active user) | `DashboardController` |
| My Profile | `admin/profile` | (any authenticated user) | `ProfileController` |
| Users | `admin/users` | `users.view` / `.create` / `.update` / `.delete` | `UserController` |
| Roles & Permissions | `admin/roles` | `roles.manage` | `RoleController` |
| Settings | `admin/settings/{group}` | `settings.manage` | `SettingController` |
| Theme Customizer | `admin/theme-customizer` | `theme.manage` | `ThemeCustomizerController` |
| Header Builder | `admin/header-builder` | `builder.header` | `HeaderBuilderController` |
| Footer Builder | `admin/footer-builder` | `builder.footer` | `FooterBuilderController` |
| Pages (Page Builder) | `admin/pages` | `pages.manage` | `PageController` |
| Sections (shared AJAX) | `admin/sections` | area-derived (`builder.header`/`builder.footer`/`pages.manage`) | `SectionController` |
| Block Library (Global Blocks) | `admin/global-blocks` | `blocks.manage` | `GlobalBlockController` |
| Menus (Menu Builder) | `admin/menus`, `admin/menu-items` | `menus.manage` | `MenuController`, `MenuItemController`, `MenuReferenceController` |
| Blog | `admin/posts`, `admin/blog-categories` | `blog.manage` | `PostController`, `BlogCategoryController` |
| Forms (Form Builder) | `admin/forms` | `forms.manage` | `FormController` |
| Enquiries | `admin/enquiries` | `enquiries.manage` | `EnquiryController` |
| Newsletter | `admin/subscribers` | `enquiries.manage` | `SubscriberController` (admin), `Frontend\NewsletterController` (public opt-in) |
| Media Library | `admin/media`, `admin/media-folders` | `media.manage` | `MediaController`, `MediaFolderController` |
| SEO — Redirects | `admin/redirects` | `seo.manage` | `RedirectController` |
| SEO — 404 Manager | `admin/not-found` | `seo.manage` | `NotFoundLogController` |
| SEO — Sitemap/Robots | `/sitemap.xml`, `/robots.txt` (public, generated) | — | `Frontend\SeoController` |
| Activity & Login Logs | `admin/activity` | `activity.view` | `ActivityLogController` |

## Module notes

**Dashboard** — landing screen after login, no module-specific permission
(every active user sees it; content should be trimmed per-role in a future
pass if role-specific KPIs are wanted).

**Settings** — schema-driven from `config/settings.php`. Four groups today:
Business Information, Branding, Social Media, SEO Defaults. See
`docs/settings-and-permissions.md`.

**Theme Customizer** — the single screen controlling site-wide design:
typography (15 roles), 24 color tokens, buttons, spacing, radius, shadows,
animations, custom CSS/JS per breakpoint, dark mode, import/export/backup.
See `docs/theme-system.md` — this is the largest, most config-driven module.

**Header/Footer Builder + Sections** — both areas are `Layout` singletons
(`layouts.area = 'header'|'footer'|'topbar'`) that own an ordered set of
polymorphic `Section` rows. `SectionController` is area-gated: it looks at
the section's owner (`Layout` vs `Page`) to decide which permission applies,
so one shared AJAX endpoint serves three different builder screens without
three copies of the same CRUD logic.

**Block Library (Global Blocks)** — a reusable-template layer on top of the
existing Section engine, not a second one. A `GlobalBlock` wraps any
existing `SectionType`'s payload (same `data_desktop`/`tablet`/`mobile`/
`style` JSON shape, same field schema, same validation) as a named,
versioned, categorizable master. "Add Existing Block" in the Page Builder
creates a `Section` instance linked to the master (`global_block_id` +
`is_synced=true`); editing the master and choosing "Update all pages"
pushes the new payload to every linked instance and flushes their page/
layout caches. "Detach From Global" (`is_synced=false`) freezes an
instance's current content locally — the link is remembered for audit
("used on N pages") but future master edits no longer reach it; "Reattach"
re-links and immediately pulls the master's current content. Any local
section can be "promoted" to a new master via the section card's cubes
icon. Deleting a master never deletes content — every linked instance
survives as a plain local section with whatever payload it last had.
Version history snapshots the pre-save state on every master edit
(`global_block_versions`), restorable with one click (which itself pushes
to linked pages, same as a normal "Update all pages" save). Export/Import
round-trips a block as JSON (imports land as a draft for review before
activating). `blocks.manage` → Admin, Content Manager.

**Page Builder** — pages are draft/published with a reserved `home` slug
that cannot be deleted. 20 section types available on pages (a superset of
what's allowed in header/footer — see `SectionType::allowedAreas()`).

**Menu Builder** — supports 8 link types (URL, Custom, Page, Blog Post,
Blog Category, Anchor, Phone, Email), 3-level
nesting, mega-menu columns (links/image/
categories/custom HTML/CTA), per-item badges, permission/auth/guest
visibility, and per-device visibility. "Location" (header/footer/mobile/
quick-links/sidebar/mega/custom) is a **label only** — any menu can be
assigned to any builder slot via the `menu_id` field on the relevant
section type, the location tag doesn't restrict it.

**Forms / Enquiries** — Form Builder is fully generic (build any form from
9 field types); Enquiries is a separate, purpose-built inbox that specific
forms (flagged with `enquiry_source`) feed into automatically. A form not
flagged with a source stays in Form Submissions only — nothing is forced.

**404 Manager ↔ Redirect Manager integration** — every real public-site 404
(GET requests outside `/admin`) is captured in `not_found_logs` (deduped by
path, hit-counted). The 404 Manager screen's "Redirect" button deep-links
into the Redirect Manager's create form with the path prefilled; saving a
redirect for that path auto-marks the 404 log resolved (`Redirect::booted()`
listens for this).

**Activity & Login Logs** — DB-backed (`activity_logs` table), separate
from but parallel to the file-based `storage/logs/security-*.log` channel.
The file log is for ops tailing/long-term forensic retention (90 days); the
DB table is for admin-visible browsing/filtering (by event, user, date
range) and has no automatic pruning yet (see future-development.md).
