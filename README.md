# Ekdanta CMS

A custom Laravel 10 CMS: page builder, header/footer builder, menus, media
library, blog, form builder + enquiry inbox, theme customizer, design system
and SEO tooling. Everything on the public site is edited from the admin panel.

This is a blank base — no pages, no media, no demo content. Build the site
from **Admin → Pages**.

## Requirements

- PHP 8.2+
- MySQL 8
- Node 18+ (asset build)
- Composer

## Local setup

```bash
composer install
cp .env.example .env      # set DB_DATABASE / DB_USERNAME / DB_PASSWORD
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Sign in at `/cms/login`:

| Email | Password |
|---|---|
| `admin@ekdanta.local` | `password` |

**Change that password immediately** — it is a seeded default.

## What ships after seeding

- Roles + permissions, one Super Admin user
- Theme + design-system defaults (typography, colors, gradients, buttons)
- Global block categories, the default enquiry forms
- A starter header (logo + Main Menu with Home/Blogs) and footer
  (about, links, contact, copyright)
- One blank published **Home** page

## Documentation

See [docs/README.md](docs/README.md) for architecture, database, admin
modules, theme system, media library, permissions and how to add a new
module or section type. Deployment steps are in [DEPLOYMENT.md](DEPLOYMENT.md).

## Tests

```bash
php artisan test
```
