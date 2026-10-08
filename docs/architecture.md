# Architecture & Folder Structure

## Layering

```
Route → Controller (thin) → FormRequest (validation) → Service (business logic)
                                                       → Repository (Eloquent, contract-bound)
                                                       → Model
```

- **Controllers** never contain business logic — they validate (via
  FormRequest or inline `$request->validate()`), call a Service method, and
  return a view/redirect/JSON response.
- **Services** hold the actual logic: creating a page, syncing section
  media, generating theme CSS, resolving a menu item's URL. Services are
  where you look first when debugging "why did this happen".
- **Repositories** are the only place Eloquent queries for a module's
  primary model live, behind an interface bound in
  `app/Providers/RepositoryServiceProvider.php`. Not every model has one —
  only models with a repository contract (see `database.md` for the list).
  Simpler modules (Enquiries, Activity Logs, 404 Manager) query the Eloquent
  model directly from the controller/service — the repository layer is used
  where a module has meaningfully complex read patterns (page rendering,
  menu trees, post listings), not applied mechanically everywhere.

## Folder structure (`app/`)

```
app/
├── Console/Commands/       Artisan commands (gsm:backup, gsm:publish-scheduled-posts)
├── Enums/
├── Exceptions/Handler.php  404 Manager capture hook lives here (render() override)
├── Helpers/helpers.php     Global functions: setting(), theme(), theme_css(), gsm_version()
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          One controller per admin module (see admin-modules.md)
│   │   ├── Frontend/       Public-site controllers (Page, Blog, Newsletter, FormSubmission, Seo)
│   │   └── Auth/           laravel/ui-scaffolded auth controllers
│   ├── Middleware/         EnsureUserIsActive, SecurityHeaders, HandleRedirects
│   └── Requests/           FormRequests, grouped Admin/ and top-level
├── Mail/                   Mailable classes (FormSubmissionMail — implements ShouldQueue)
├── Models/                 One file per Eloquent model (see database.md)
│   └── Concerns/           Shared traits (HasSeoMeta, GeneratesUniqueSlug-style concerns)
├── Observers/
├── Policies/
├── Providers/               RepositoryServiceProvider, SectionTypeServiceProvider, EventServiceProvider, AppServiceProvider
├── Repositories/
│   ├── Contracts/          Interfaces
│   └── Eloquent/           Implementations
├── Services/                Business logic (one per module)
│   ├── Concerns/            GeneratesUniqueSlug trait
│   └── SectionTypes/        Header/, Footer/, Page/, Shared/ — the block registry (see reusable-components.md)
└── Support/
```

## Folder structure (`resources/views/`)

```
resources/views/
├── admin/                  One directory per admin module, mirrors app/Http/Controllers/Admin/
│   ├── layouts/app.blade.php   Base admin layout (AdminLTE), all admin views extend this
│   └── partials/               Shared admin fragments (SEO fieldset, flash messages)
├── auth/                   laravel/ui auth views (login, password reset)
├── components/
│   ├── admin/               Blade components used in admin views
│   └── frontend/            Blade components used on the public site
├── emails/                  Mailable views
├── frontend/
│   ├── layouts/app.blade.php   Base public layout — loads Theme Customizer CSS/fonts, header/footer partials
│   ├── partials/               menu-item-link, mega-menu-column, seo-head, section-wrapper, header, footer, topbar…
│   ├── sections/               One Blade file per registered SectionType key (see reusable-components.md)
│   ├── blog/                   Blog index/show + partials
│   └── pages/show.blade.php    Renders any Page Builder page by slug
└── layouts/                 (legacy/shared layout fragments, if any)
```

## Request lifecycle (frontend page render)

1. `routes/web.php` — catch-all `/{slug}` (excluding reserved prefixes:
   `cms`, `admin`, `blogs`, `forms`, `storage`, `newsletter`, `search`,
   `whatsapp`, auth routes, `build`, `vendor`, `js`, `css`)
   → `Frontend\PageController@show`
2. `PageRenderService::payloadFor($slug)` — cached (`page.render.{slug}`)
   lookup of the published `Page` + its active `sections`, eager-loading
   everything a section could touch (media, menus) because
   `Model::preventLazyLoading()` is on.
3. `frontend/pages/show.blade.php` renders the SEO head block
   (`SeoService::resolve()`) then loops sections through
   `frontend/partials/section-wrapper.blade.php`, which includes
   `frontend/sections/{type}.blade.php` for each one.
4. `frontend/layouts/app.blade.php` (the outer layout) injects, in order:
   Google Fonts link, `<style id="gsm-theme-vars">` (cached `theme_css()`),
   header JS, then body content, then global/footer JS.

## New frontend route checklist

Any new top-level frontend route (e.g. `/events`, `/careers`) **must**
be added to the negative-lookahead regex in the catch-all page route
(`routes/web.php`) or the page-builder route will swallow it as a slug
lookup and 404. See `docs/future-development.md` for the exact pattern.

## Known artifacts (do not "fix" without checking history)

- `database/migrations/2014_10_12_100000_create_password_resets_table.php`
  creates an unused `password_resets` table alongside the actual active one
  (`password_reset_tokens`, per `config/auth.php`'s `passwords.users.table`).
  Harmless — an empty, unreferenced table — but don't delete the migration
  file; it may already be recorded in `migrations` on a deployed environment
  and removing it would break migration history reproducibility. If starting
  a fresh install from scratch, it's safe to `down()` drop after seeding if
  you want it gone.
