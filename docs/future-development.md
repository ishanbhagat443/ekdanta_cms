# Future Module Development

This CMS was built module-by-module with a repeatable recipe. Follow it for
consistency — deviating makes the codebase harder to maintain, not more
flexible.

## SectionTypes not yet built

The Global Block Library (see `reusable-components.md`) reuses whatever
`SectionType`s already exist in `SectionTypeServiceProvider` — it does not
add new ones. These block types are commonly requested but not yet
registered; each is a "new SectionType" task (see the recipe below), not a
Global Block Library task:

Pricing Table, Statistics (Counter already covers simple stat counters —
this would be a richer multi-column variant), Partners/Clients/Awards/
Certificates (all "logo strip" variants — Cards or Gallery may already
cover this well enough; check before building a new type), Downloads list,
Popup, Modal, Floating Button, Breadcrumb (standalone — one already renders
inline on inner pages via `page-title-banner.blade.php`; a dedicated
SectionType would let it be repositioned/restyled independently), Page
Header (as a distinct block from the built-in page banner), Instagram
Feed (needs an API integration decision — Instagram Basic Display API
requires OAuth + token refresh, scope this carefully before starting),
Blog Slider, Latest Blogs (both are "pull N items from an existing module
and render as a slider/grid"), Contact Information (a dedicated block reading
Settings → Business Information, distinct from the existing
`footer-contact` widget which is footer-only).

## The recipe (adding a whole new module, e.g. "Testimonials CMS")

1. **Migration** — one file, additive where possible. Prefer a flat table
   over polymorphic unless genuinely reused across 3+ models (see `seo_meta`,
   `mediables` for when polymorphism paid off).
2. **Model** — fillable, casts, relationships. Add a `scopeXxx()` for any
   filter used more than once.
3. **Repository** (only if the module has non-trivial read patterns —
   listings with filters, trees, cached renders). Contract in
   `app/Repositories/Contracts/`, implementation in `app/Repositories/Eloquent/`,
   bind both in `RepositoryServiceProvider::$bindings`.
4. **Service** — all business logic. Controllers call this, never Eloquent
   directly for anything beyond a simple `find()`/`paginate()`.
5. **FormRequest** (or inline `$request->validate()` for small controllers)
   — validation rules should mirror any config-schema (`config('module.field.rules')`)
   if the module is schema-driven, so form/validation/storage always agree.
6. **Controller** — thin. `Admin\{Module}Controller` for the admin CRUD,
   `Frontend\{Module}Controller` only if there's a public-facing route.
7. **Routes** — add to `routes/admin.php` under a clearly commented section
   matching the existing style. If you add a **frontend** top-level route
   (not nested under an existing prefix), add it to the negative-lookahead
   regex in the page catch-all route in `routes/web.php` — forgetting this
   is the single most common integration bug in this codebase's history
   (it silently 404s or, worse, gets swallowed as a page slug lookup).
8. **Permission** — add to `PermissionSeeder::$map`, re-run the seeder.
9. **Sidebar entry** — `config/adminlte.php`, gated by the same permission.
10. **Views** — extend `admin.layouts.app`. Reuse existing partials before
    writing new ones (`admin/partials/seo-fields.blade.php` for any
    SEO-capable model, the Settings/Theme field-partial pattern for any
    config-schema-driven form).
11. **Tests** — one Feature test class, covering: permission gate, the
    happy path CRUD, at least one validation-rejection case, and (if
    public-facing) the frontend render. Run the **full** suite before
    calling it done, not just the new file — this codebase has caught real
    cross-module regressions this way multiple times (see: password policy
    hardening breaking two old auth tests, WebP addition needing a media
    test update).
12. **Memory / docs** — update this `docs/` set if the module changes a
    cross-cutting pattern (new permission convention, new cache key
    family, new reusable partial).

## Adding a field to an existing config-schema module (Settings, Theme, any SectionType)

This is the common case and should need **zero migration**:
1. Add the key to `config/settings.php` / `config/theme.php` /
   the relevant `SectionType::fields()` array, with a `type`, `label`, and
   (for Settings/Theme) `rules` or a type-inferred default rule.
2. The existing generic field-renderer partial picks it up automatically.
3. Done. The value persists as a new row in the existing key-value table.

## Adding a new SectionType (page/header/footer block)

1. Create `app/Services/SectionTypes/{Area}/{Name}Section.php` extending
   the right base (`SectionType`, or `PageSectionType` for page-only blocks
   which gets `headingFields()`/`buttonsRepeater()`/`mediaField()` helpers
   for free).
2. Create `resources/views/frontend/sections/{key}.blade.php` — must match
   `key()` exactly.
3. Register the class in `SectionTypeServiceProvider::$types`, in the
   logical position (header/footer/shared/page groups are kept together).
4. No route, no controller, no migration — the polymorphic `sections`
   table and the shared `SectionController` already handle it.

## Newsletter admin screen — done

Built during the final integration pass: `Admin\SubscriberController`
(`/admin/subscribers` — search, delete, CSV export with UTF-8 BOM),
gated on `enquiries.manage` (same permission as Enquiries — Marketing
already has it). Follows the Enquiries module's filtered-index + export
pattern exactly.

## Activity Log retention (known gap)

`ActivityLogService::prune(int $days = 180)` exists but isn't scheduled
anywhere yet. Add `$schedule->call(fn () => app(ActivityLogService::class)->prune())->weekly();`
to `App\Console\Kernel::schedule()` when the table's expected growth
warrants it.

## Two-Factor Authentication (scoped as "ready", not built)

`users.two_factor_secret`, `two_factor_recovery_codes`,
`two_factor_confirmed_at` columns exist; the profile screen has a disabled
toggle. To actually implement:
1. `composer require pragmarx/google2fa-laravel`
2. Add QR-provisioning + confirmation flow to `ProfileController`
   (generate secret → show QR → verify code → save `two_factor_secret` +
   set `two_factor_confirmed_at`).
3. Add a login-time challenge: after password auth succeeds, if
   `two_factor_confirmed_at` is set, redirect to a 2FA challenge screen
   before establishing the full session (Laravel Fortify's 2FA flow is a
   good reference implementation if you don't want to hand-roll it).
4. Generate + store hashed recovery codes; add a "regenerate recovery
   codes" action.
5. Un-disable the profile toggle once the flow is real.

## Testing conventions to preserve

- `use RefreshDatabase;` + SQLite `:memory:` (never touches the dev MySQL
  DB — this is configured in `phpunit.xml`, don't change it to point at a
  real database).
- Permission tests always check the **forbidden** case for a plain user
  AND the **success** case for the right role — one without the other has
  caught real bugs in this codebase (a permission that gates nothing, or a
  route that isn't actually gated).
- `Model::preventLazyLoading()` is on in testing — a green test suite is
  proof no view lazy-loads a relation, don't disable this guard to make a
  red test pass; fix the eager-loading instead.
- Never run `php artisan config:cache`/`route:cache` before running tests
  locally — see `docs/README.md` "Live environment notes".
