<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Page;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Services\SettingsService;

/**
 * Special jobs a page can hold: the home page, the 404 page and the
 * thank-you page. Each role points at one page (a setting, so two pages can
 * never claim the same role), chosen in the page's own settings.
 *
 * A role takes effect on the public site only once its page is published;
 * previews use it either way.
 */
final class PageRoles
{
    public const ROLES = [
        'home' => 'Home page',
        'not_found' => '404 page (shown for every missing address)',
        'thank_you' => 'Thank-you page (where forms can send visitors)',
    ];

    /** The page holding a role, or null. */
    public static function page(string $role, bool $publishedOnly = true): ?Page
    {
        $id = (int) setting("pages.{$role}_id", 0);

        if ($id === 0) {
            return null;
        }

        $page = Page::query()->find($id);

        return $page && (! $publishedOnly || $page->isPublished()) ? $page : null;
    }

    /** The role a page holds, or null. */
    public static function roleOf(Page $page): ?string
    {
        foreach (array_keys(self::ROLES) as $role) {
            if ((int) setting("pages.{$role}_id", 0) === $page->id) {
                return $role;
            }
        }

        return null;
    }

    /** Give $page a role (taking it from any other page), or none. */
    public static function assign(Page $page, ?string $role): void
    {
        $settings = app(SettingRepositoryInterface::class);

        foreach (array_keys(self::ROLES) as $key) {
            $holder = (int) setting("pages.{$key}_id", 0);

            if ($key === $role) {
                $settings->upsertValue('pages', "{$key}_id", (string) $page->id, 'number');
            } elseif ($holder === $page->id) {
                $settings->upsertValue('pages', "{$key}_id", null, 'number');
            }
        }

        app(SettingsService::class)->flushCache();
    }

    /** Ids of pages that must stay out of the sitemap (404, thank-you). */
    public static function hiddenFromSitemap(): array
    {
        return array_values(array_filter([
            (int) setting('pages.not_found_id', 0),
            (int) setting('pages.thank_you_id', 0),
        ]));
    }
}
