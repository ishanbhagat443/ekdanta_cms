# Ekdanta CMS — Documentation

A custom Laravel 10 CMS. Everything visible on the public site is editable from
the admin panel — nothing is hardcoded except application logic itself.

## Index

| Document | Covers |
|---|---|
| [architecture.md](architecture.md) | Folder structure, request lifecycle, module map |
| [database.md](database.md) | Every table, its purpose, and relationships |
| [admin-modules.md](admin-modules.md) | Every admin screen, its permission gate, and what it controls |
| [reusable-components.md](reusable-components.md) | Section Type Registry, Blade partials, JS components |
| [theme-system.md](theme-system.md) | Theme Customizer — typography, colors, CSS variable generation |
| [media-library.md](media-library.md) | Upload pipeline, variants, picker component |
| [settings-and-permissions.md](settings-and-permissions.md) | Settings schema, roles, permission matrix |
| [future-development.md](future-development.md) | How to add a new module, section type, or settings group |
| `../DEPLOYMENT.md` | Server requirements, deploy steps, production checklist |

## What this CMS is

Think of it as a lightweight WordPress: one admin panel controls header,
footer, every page's layout, navigation menus, the full color/type/spacing
system, blog, lead-capture forms, and SEO — all without touching code. It
ships empty: no pages, no media, no demo content. Build the site from
Admin → Pages.

## Architecture at a glance

- **Laravel 10**, PHP 8.1+, MySQL, AdminLTE 3 (admin), Bootstrap 5 (frontend)
- **Layered**: Controller (thin) → FormRequest (validation) → Service (business
  logic) → Repository (Eloquent queries, contract-bound) → Model
- **Config-driven schemas**: Settings, Theme Customizer, and every builder's
  field set are declared in a `config/*.php` file, not scattered across
  migrations/views. Adding a field to an existing group is a config change,
  not a migration.
- **Polymorphic sections engine**: header/footer/page layouts share one
  `sections` table + a `SectionType` registry — one new PHP class + one Blade
  file adds a whole new block type usable in header, footer, or any page.
- **RBAC**: Spatie `laravel-permission`, 6 roles, `Gate::before` Super Admin
  bypass, permission-per-module (`{module}.manage` convention).
- **187+ automated tests** (`php artisan test`) covering every module —
  treat a red test suite as a merge blocker, not a formality.

## Where to start reading

New to this codebase? Read in this order:
1. `architecture.md` — orient yourself in the folder structure
2. `database.md` — understand what data exists
3. `admin-modules.md` — see what an admin can actually do
4. Pick the module you're changing and read its memory entry / relevant service class

## Live environment notes

- Dev: WAMP, MySQL `gsm_cms`, `php artisan serve` or Apache vhost
- `Model::preventLazyLoading()` is **on** outside production — any Blade
  touching an un-eager-loaded relation throws immediately in dev/test, by
  design. Fix by eager-loading, never by disabling the guard.
- Tests run against SQLite `:memory:` (`phpunit.xml`) — never against the
  dev MySQL database.
- **Never run `config:cache`/`route:cache` in a session where you're also
  running the test suite** — cached config freezes `.env` values and ignores
  `phpunit.xml`'s test-environment overrides, which breaks every test in ways
  that look like application bugs but aren't. Clear caches
  (`php artisan config:clear && php artisan route:clear`) before testing
  locally; only cache in a real production deploy.
