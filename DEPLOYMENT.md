# Ekdanta CMS — Deployment Guide

See also `docs/README.md` for architecture, database, module, and theme-system documentation.

## Local development warning

**Never leave `route:cache`/`config:cache`/`view:cache` active while running
the test suite locally.** Cached config freezes whatever `.env` currently
says and ignores `phpunit.xml`'s test-environment overrides (SQLite
`:memory:`, `APP_ENV=testing`, etc.) — every test breaks in ways that look
like application bugs but are actually stale cache. Always:
```bash
php artisan config:clear && php artisan route:clear && php artisan view:clear
php artisan test
```
Only run the cache commands as the last step of an actual production
deploy (see "After every deploy" below).

## Server requirements

- PHP 8.1+ with extensions: `gd` (image variants + WebP), `zlib` (gzip backups),
  `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `json`, `fileinfo`
- MySQL 5.7+ / MariaDB 10.3+ (`mysqldump` in PATH for backups)
- Composer 2, Node 18+ (build step only)
- Web root must point at `public/` — never at the project root

## First deployment

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env            # then edit — see below
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force     # roles, permissions, settings, starter header/footer + blank Home
npm ci && npm run build
```

No `php artisan storage:link` — uploads are written straight into the upload
root (`public/uploads` by default, or `CMS_UPLOAD_PATH`), so no symlink is
involved. See "File storage" below.

**Seeding ships no logo.** The header/footer logo sections come up with
`media_id = null`; upload a logo in Media Library and pick it in the Header
and Footer Builders (and under Settings → Branding).

### Production .env checklist

| Key | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` (never true in production) |
| `APP_URL` | `https://your-domain.com` |
| `LOG_CHANNEL` | `daily` |
| `LOG_LEVEL` | `error` |
| `QUEUE_CONNECTION` | `database` |
| `SESSION_DRIVER` | `file` (or `redis` if available) |
| `SESSION_SECURE_COOKIE` | `true` (HTTPS only) |
| `CACHE_DRIVER` | `file` (or `redis`) |
| `MAIL_*` | real SMTP credentials — form notifications depend on it |
| `CMS_UPLOAD_PATH` | optional — absolute path if uploads live outside the app |
| `CMS_UPLOAD_URL` | optional — public URL that maps to `CMS_UPLOAD_PATH` |

A ready-to-edit template ships as `.env.production.example`.

Also set the **Canonical Base URL** under Admin → Settings → SEO Defaults;
sitemap.xml and canonical tags build from it.

### File storage

Uploads (media library, branding, form attachments, theme fonts) all resolve
through one place — `mm_upload_root()` in `app/Helpers/helpers.php`, fed by
`config/gsm.php` → `CMS_UPLOAD_PATH`. The `public` filesystem disk points at the
same value, so every `Storage::disk('public')` call follows automatically.

- Unset → `public/uploads` (works everywhere, no symlink).
- Uploads outside the app (e.g. shared hosting web root):
  ```
  CMS_UPLOAD_PATH=/home/USERNAME/public_html/uploads
  CMS_UPLOAD_URL="${APP_URL}/uploads"
  ```
  The web server must actually serve `CMS_UPLOAD_PATH` at `CMS_UPLOAD_URL`.

`public/robots.txt` must NOT exist — a static file there shadows the dynamic
`/robots.txt` route and the CMS-managed rules (Admin → Settings → SEO) are
silently ignored.

## After every deploy

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart       # workers pick up new code
```

## Background processes (required)

1. **Scheduler** — cron entry (single line):
   ```
   * * * * * cd /path/to/gsm-cms && php artisan schedule:run >> /dev/null 2>&1
   ```
   Drives: scheduled blog publishing (every minute), nightly DB backup (02:30),
   failed-job pruning (weekly).

2. **Queue worker** — form notification mail is queued. Supervisor program:
   ```
   command=php /path/to/gsm-cms/artisan queue:work --sleep=3 --tries=3 --max-time=3600
   ```
   (Windows: Task Scheduler running `php artisan queue:work --stop-when-empty`
   every minute is an acceptable substitute.)

## Backups

- `php artisan gsm:backup` — gzipped `mysqldump` to `storage/app/backups`,
  14-day retention (`--keep=N` to change). Scheduled nightly at 02:30.
- Also back up the upload root (`public/uploads`, or whatever `CMS_UPLOAD_PATH`
  points at) at the infrastructure level — uploaded media is not covered by
  the DB dump.

## Security notes

- Security headers are set by `App\Http\Middleware\SecurityHeaders` (web group);
  HSTS is emitted automatically on HTTPS.
- Auth events (login success/failure/lockout/logout) log to BOTH
  `storage/logs/security-*.log` (90-day rotation, ops tailing) AND the
  `activity_logs` table (browsable at `/cms/activity`, `activity.view`
  permission) — two independent trails, neither replaces the other.
- Login is throttled (5 attempts → lockout). Public forms + newsletter POST at 10 req/min/IP.
- All state-changing routes are CSRF-protected (Laravel `VerifyCsrfToken`, no exclusions).
- Password policy is hardened in production (`AppServiceProvider::boot()`):
  min 8 chars, mixed case, numbers, and a HaveIBeenPwned "uncompromised"
  check — the uncompromised check is skipped outside production so
  local/CI runs never make an external network call.
- Two-Factor Authentication is schema-ready but not yet implemented (see
  `docs/future-development.md` for the build-out steps) — the profile
  screen shows a disabled toggle, not a working feature.
- 404s on the public site are logged (`not_found_logs`) and can be
  converted into 301 redirects from `/cms/not-found` in one click.
- Keep `APP_DEBUG=false`; debug pages leak configuration.

## Caches in play (flush knowledge)

| Cache | Key(s) | Invalidated by |
|---|---|---|
| Settings | `settings.all` | saving Settings in admin |
| Header/Footer render | layout payloads | builder saves |
| Page render | `page.render.{slug}` | page/section saves |
| XML sitemap | `seo.sitemap` (1 h TTL) | page saves, or expiry |
| Redirect map | `seo.redirects.map` | redirect create/update/delete |

`php artisan cache:clear` nukes everything if in doubt.
