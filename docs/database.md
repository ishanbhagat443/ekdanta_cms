# Database

MySQL, `Schema::defaultStringLength(191)` set globally (WAMP `utf8mb4`
1000-byte index limit workaround — see `AppServiceProvider::boot()`).

## Tables, in build order

| Table(s) | Migration | Purpose |
|---|---|---|
| `users` | `2014_10_12_000000` | Admin users (also front-end auth-gated menu items use this) |
| `password_reset_tokens` | `2014_10_12_100000` | **Active** password-reset store (see `config/auth.php`) |
| `failed_jobs` | `2019_08_19` | Queue failure log |
| `personal_access_tokens` | `2019_12_14` | Sanctum API tokens (unused by the admin panel today, available for future API work) |
| `password_resets` | `2014_10_12_100000` (dup timestamp) | **Dead/unused** — see architecture.md "Known artifacts" |
| Spatie permission tables (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`) | `2026_07_07_073349` | RBAC (package migration) |
| `users.is_active` | `2026_07_07_080401` | Enable/disable an admin account |
| `settings` | `2026_07_07_081117` | Settings module — group/key/value rows, schema in `config/settings.php` |
| `media_folders`, `media` | `2026_07_14_000001` | Media Library — folder tree + files with generated variants |
| `layouts`, `sections`, `menus`, `menu_items` | `2026_07_14_000002` | Header/Footer Builder core + the original Menu Builder tables |
| `subscribers` | `2026_07_14_000003` | Newsletter signups |
| `pages` | `2026_07_14_000004` | Page Builder pages (slug, draft/published, banner, soft-deletes) |
| `seo_meta`, `mediables` | `2026_07_14_000005` | Polymorphic SEO metadata + ordered polymorphic media attachments, reused by pages/posts |
| `blog_categories`, `blog_tags`, `posts`, `blog_category_post`, `blog_tag_post`, `related_posts` | `2026_07_15_000002` | Blog module |
| `forms`, `form_fields`, `form_submissions` | `2026_07_15_000003` | Form Builder (9 field types) |
| `enquiries` + `forms.enquiry_source` | `2026_07_15_000004` | Central enquiry inbox, bridged from any Form Builder form |
| `redirects` + `seo_meta` extensions (Twitter Card, breadcrumb, sitemap controls) | `2026_07_15_000005` | 301/302 Redirect Manager + enterprise SEO fields |
| `jobs` | `2026_07_15_185054` | Queue (`QUEUE_CONNECTION=database`) |
| `menus`/`menu_items` extensions (location, 10 link types, mega-menu columns, badges, visibility) | `2026_07_16_000001` | Menu Builder upgrade — additive, did not break the original schema |
| `theme_settings`, `theme_fonts`, `theme_presets` | `2026_07_17_000001` | Theme Customizer (schema-less group/key/value, mirrors `settings`) |
| `activity_logs` + `users` 2FA columns | `2026_07_18_000001` | DB-backed activity/login log; 2FA-ready schema (dormant) |
| `not_found_logs` | `2026_07_18_000002` | 404 Manager, feeds the Redirect Manager |
| `global_block_categories`, `global_blocks`, `global_block_versions` + `sections.global_block_id`/`is_synced` | `2026_07_19_000001` | Global Block Library — reusable Section templates, version history, sections linked to their master |

## Key relationships

- **Polymorphic `seo_meta`**: `metable_type` + `metable_id` → Page or Post
  (and any future module). One table, one `HasSeoMeta` trait, reused
  everywhere SEO fields are needed.
- **Polymorphic `mediables`**: `mediable_type` + `mediable_id` + `collection`
  (`gallery`|`downloads`) + `sort_order` — lets any model attach ordered,
  named media collections without a dedicated pivot per collection type.
- **Polymorphic `sections`**: `sectionable_type` + `sectionable_id` → either
  a `Layout` (header/footer singleton) or a `Page`. One `sections` table
  drives three different builder screens.
- **`menu_items` self-referencing tree**: `parent_id` → `menu_items.id`,
  max depth 3 (enforced in `MenuService::MAX_DEPTH`), `reference_type` +
  `reference_id` polymorphically point at Page/Post/BlogCategory for the
  3 "pick from content" link types.
- **`theme_settings` / `settings`**: intentionally **not** polymorphic or
  foreign-keyed to anything — flat `group`+`key` unique pairs, values always
  strings/JSON-encoded. This is deliberate: it lets `config/settings.php`
  and `config/theme.php` be the single source of truth for what fields
  exist, with zero migrations needed to add a field.
- **`sections.global_block_id` → `global_blocks.id`** (nullable,
  `nullOnDelete`): links an on-page/layout `Section` instance to the
  reusable master it was placed from. `is_synced` (boolean) governs whether
  the instance still receives the master's updates — deliberately a column
  on `sections`, not a separate join table, because a section is either
  linked-and-synced, linked-and-detached, or plain-local; there's no
  many-to-many relationship to model here (one instance links to exactly
  one master, or none). `global_blocks` reuses `sections`' own JSON payload
  shape (`data_desktop`/`tablet`/`mobile`/`style`) rather than inventing a
  parallel one, specifically so the existing `SectionType` field schema,
  validation, and Blade rendering pipeline work against a `GlobalBlock`
  exactly as they do against a `Section`.

## Indexing

Every module with a public-listing query has a composite index matching its
actual `WHERE` clause, not just single-column indexes on foreign keys:

- `posts`: `[status, published_at]` compound index (the exact shape of the
  "live/published" query), plus `views_count` for the "popular" sort.
- `menu_items`: `[menu_id, parent_id, sort_order]` — the tree-fetch query.
  `activity_logs`: `[event, created_at]` and `[subject_type, subject_id]`.
- `not_found_logs`: `[resolved, last_hit_at]` — the "unresolved, most
  recent" admin listing query.
- All pivot tables carry a unique compound index on their two foreign keys
  (plus `collection` for `mediables`), which both enforces the business
  rule (no duplicate attach) and serves as the join index.

## Caching

Nothing here is queried on every request without a cache layer in front of
it. See `docs/theme-system.md` and `../DEPLOYMENT.md` "Caches in play" for
the full list of cache keys and what invalidates them.
