<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Menu;
use App\Models\Page;
use App\Services\SectionTypes\SectionType;
use App\Services\SectionTypes\SectionTypeRegistry;
use App\Support\SectionAnimation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Turns a starter layout (config/page_starters.php) into real sections.
 *
 * What it produces is ordinary CMS content: nothing stays linked to the
 * starter, so the user edits a finished page rather than fighting a template.
 */
class PageStarterService
{
    public function __construct(
        protected PageService $pages,
        protected SectionTypeRegistry $registry,
        protected PageRenderService $renderer,
    ) {
    }

    /** @return Collection<string, array<string, mixed>> */
    public function starters(): Collection
    {
        return collect((array) config('page_starters.pages', []));
    }

    /** @return Collection<string, array<string, mixed>> */
    public function kits(): Collection
    {
        return collect((array) config('page_starters.kits', []));
    }

    public function starter(string $key): ?array
    {
        return config("page_starters.pages.{$key}");
    }

    /**
     * Fill a page with a starter's sections.
     *
     * Refuses to run over existing content: a starter is for an empty page,
     * and silently burying someone's work under a template would be the worst
     * kind of helpful.
     */
    public function apply(Page $page, string $starterKey): Page
    {
        $starter = $this->starter($starterKey);

        if ($starter === null || $page->sections()->exists()) {
            return $page;
        }

        DB::transaction(function () use ($page, $starter): void {
            foreach ((array) ($starter['sections'] ?? []) as $order => $definition) {
                $this->createSection($page, $definition, $order);
            }
        });

        $this->renderer->flushCache($page);

        return $page->refresh();
    }

    /**
     * Create every page in a kit, then wire them into the main menu.
     *
     * Existing pages with the same slug are left alone and skipped — running a
     * kit twice must not duplicate or overwrite anything.
     *
     * @return array{created: list<Page>, skipped: list<string>}
     */
    public function applyKit(string $kitKey): array
    {
        $kit = config("page_starters.kits.{$kitKey}");

        if ($kit === null) {
            return ['created' => [], 'skipped' => []];
        }

        $created = [];
        $skipped = [];

        foreach ((array) ($kit['pages'] ?? []) as $spec) {
            $slug = $spec['slug'] ?? null;

            // Live pages only. A soft-deleted page holding the slug must not
            // be adopted: filling it would leave the kit's page in the bin,
            // invisible on the site and in the Pages list. createPage()'s
            // unique-slug logic gives the new page a free slug instead.
            $existing = $slug ? Page::where('slug', $slug)->first() : null;

            // An existing page keeps whatever is on it — unless it is empty,
            // in which case filling it is exactly what the user asked for.
            if ($existing && $existing->sections()->exists()) {
                $skipped[] = $existing->title;

                continue;
            }

            $page = $existing ?: $this->pages->createPage([
                'title' => $spec['title'] ?? 'Untitled',
                'slug' => $slug,
                'status' => Page::STATUS_PUBLISHED,
            ]);

            // An existing page keeps everything about itself — including
            // whether it is published. Filling an empty page with content is
            // what the user asked for; putting their draft live is not.
            // Newly created pages are published, since a kit exists to produce
            // a working site.
            $created[] = $this->apply($page->refresh(), $spec['starter'] ?? 'blank');
        }

        // Menu entries follow the pages that were ACTUALLY created: a slug
        // taken since the kit was written gets de-duplicated to about-us-2,
        // and looking up the configured slug would quietly find nothing.
        $this->addToMainMenu($created);

        return ['created' => $created, 'skipped' => $skipped];
    }

    /* ── Internals ───────────────────────────────────────────────── */

    /** @param array<string, mixed> $definition */
    private function createSection(Page $page, array $definition, int $order): void
    {
        $key = (string) ($definition['type'] ?? '');

        // A starter naming a section type this install does not have is
        // skipped rather than fatal: the rest of the page is still worth having.
        if (! $this->registry->has($key)) {
            return;
        }

        $type = $this->registry->find($key);

        $page->sections()->create([
            'type' => $definition['type'],
            'name' => $definition['name'] ?? $type->label(),
            'sort_order' => $order,
            'is_active' => true,
            'data_desktop' => $this->payloadFor($type, (array) ($definition['data'] ?? [])),
            'style' => $this->styleFor($definition),
        ]);
    }

    /**
     * Merge the starter's values over the section type's own defaults, so a
     * starter only has to name the fields it actually cares about.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function payloadFor(SectionType $type, array $values): array
    {
        return array_replace($type->defaults(), $values);
    }

    /**
     * Starters describe animation in shorthand ('anim' => 'fade-up'); expand
     * it into the stored shape, with tablet and mobile following desktop.
     *
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function styleFor(array $definition): array
    {
        $style = (array) ($definition['style'] ?? []);

        if (empty($definition['anim'])) {
            return $style;
        }

        $style['anim'] = SectionAnimation::sanitiseAll([
            'desktop' => [
                'preset' => $definition['anim'],
                'stagger' => $definition['stagger'] ?? 0,
            ],
            'tablet' => ['preset' => SectionAnimation::INHERIT],
            'mobile' => ['preset' => SectionAnimation::INHERIT],
        ]);

        return $style;
    }

    /**
     * Add the kit's pages to the main menu, skipping any URL already there so
     * re-running a kit does not double up the navigation.
     *
     * @param  list<Page>  $pages
     */
    private function addToMainMenu(array $pages): void
    {
        if ($pages === []) {
            return;
        }

        $menu = Menu::query()->where('location', 'header')->orderBy('id')->first();

        if ($menu === null) {
            return;
        }

        $existing = $menu->items()->pluck('url')->all();
        $order = (int) $menu->items()->max('sort_order');

        foreach ($pages as $page) {
            $url = $page->slug === 'home' ? '/' : '/'.$page->slug;

            if (in_array($url, $existing, true)) {
                continue;
            }

            $menu->items()->create([
                'label' => $page->title,
                'link_type' => 'url',
                'url' => $url,
                'sort_order' => ++$order,
            ]);

            $existing[] = $url;
        }

        app(LayoutService::class)->flushCache();
    }
}
