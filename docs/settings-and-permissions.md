# Settings & Permissions

## Settings module

Schema-driven, `config/settings.php` is the single source of truth. Four
groups today, each a tab at `/admin/settings/{group}`:

| Group | Fields |
|---|---|
| `business` | company_name, email, support_email, phone, whatsapp, address, google_map, latitude, longitude, gst, pan, working_hours |
| `branding` | logo_light, logo_dark, favicon, logo_email, logo_login, watermark |
| `social` | facebook, instagram, linkedin, twitter, youtube, pinterest, threads, telegram, whatsapp_link |
| `seo` | google_analytics, google_tag_manager, meta_title, meta_description, meta_keywords, og_title, og_description, og_image, twitter_handle, schema_json, robots, canonical |

Values persist as `group`/`key`/`value` rows in `settings`, cached forever
under `settings.all`, flushed on every save. Read via the `setting()` /
`setting_image()` helpers anywhere in the codebase.

## Roles

Defined in `config/gsm.php` (`roles` array), seeded by `RoleSeeder`:

1. **Super Admin** — bypasses every permission check (`Gate::before`); no
   permission rows exist for this role in the seeder by design.
2. **Admin** — the working "owns everything" role; has every module
   permission.
3. **SEO Manager** — `media.manage`, `seo.manage`.
4. **Content Manager** — `builder.header`, `builder.footer`, `pages.manage`,
   `catalog.manage`, `products.manage`, `blog.manage`, `forms.manage`,
   `enquiries.manage`, `menus.manage`, `media.manage`, `blocks.manage`.
5. **Marketing** — `blog.manage`, `forms.manage`, `enquiries.manage`,
   `media.manage`.
6. **Viewer** — no permissions (read-only via whatever's not gated, i.e.
   the dashboard).

## Full permission matrix

| Permission | Admin | SEO Manager | Content Manager | Marketing |
|---|:-:|:-:|:-:|:-:|
| `settings.manage` | ✓ | | | |
| `media.manage` | ✓ | ✓ | ✓ | ✓ |
| `builder.header` | ✓ | | ✓ | |
| `builder.footer` | ✓ | | ✓ | |
| `pages.manage` | ✓ | | ✓ | |
| `blog.manage` | ✓ | | ✓ | ✓ |
| `forms.manage` | ✓ | | ✓ | ✓ |
| `enquiries.manage` | ✓ | | ✓ | ✓ |
| `menus.manage` | ✓ | | ✓ | |
| `seo.manage` | ✓ | ✓ | | |
| `theme.manage` | ✓ | | | |
| `blocks.manage` | ✓ | | ✓ | |
| `users.view`/`.create`/`.update` | ✓ | | | |
| `users.delete` | — (nobody; see note) | | | |
| `roles.manage` | — (nobody; see note) | | | |
| `activity.view` | ✓ | | | |

**Note on `users.delete`/`roles.manage`**: no role is granted these in the
seeder — only Super Admin can delete users or manage roles, via the
`Gate::before` bypass. This is intentional, not an oversight: deleting
users and editing the permission matrix are irreversible/security-critical
actions reserved for the one role that can't be locked out of anything.

## Adding a new permission

1. Add `'{module}.manage' => ['Role1', 'Role2']` to
   `database/seeders/PermissionSeeder.php`'s `$map`.
2. Run `php artisan db:seed --class=PermissionSeeder --force`.
3. Gate the controller: `$this->middleware('can:{module}.manage');`
4. Add a sidebar entry in `config/adminlte.php` with `'can' => '{module}.manage'`.

`Gate::before` is registered in `app/Providers/AuthServiceProvider.php` and
means Super Admin never needs an explicit permission row; every other role
needs to be listed. Note this only bypasses **permission gates**, not
model-level invariants — things like "can't delete yourself" or "Super
Admin role can't be edited" are still enforced at the controller/policy
level (see `UserController`/`UserPolicy`) precisely because the gate bypass
would otherwise skip them too.
