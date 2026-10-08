<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Layout;
use App\Models\Media;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class LayoutService
{
    public const CACHE_KEY = 'layout.render';

    public function __construct(protected MenuRepositoryInterface $menus)
    {
    }

    /** Get-or-create the singleton layout row for an area. */
    public function area(string $area): Layout
    {
        return Layout::query()->firstOrCreate(['area' => $area]);
    }

    public function updateArea(string $area, array $data): Layout
    {
        $layout = $this->area($area);

        $layout->update([
            'settings' => array_replace((array) $layout->settings, (array) ($data['settings'] ?? [])),
            'custom_css' => $data['custom_css'] ?? $layout->custom_css,
            'custom_js' => $data['custom_js'] ?? $layout->custom_js,
        ]);

        $this->flushCache();

        return $layout->refresh();
    }

    /**
     * Everything the frontend needs to render global chrome, cached.
     * Menus + media are resolved here so blades never query.
     */
    public function renderPayload(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $areas = [];

            foreach ([Layout::AREA_TOPBAR, Layout::AREA_HEADER, Layout::AREA_FOOTER] as $area) {
                $layout = Layout::query()
                    ->with(['sections' => fn ($q) => $q->where('is_active', true), 'sections.developerOverride'])
                    ->firstOrCreate(['area' => $area]);

                $sections = $layout->sections->map(function ($section) {
                    return [
                        'section' => $section,
                        'menu' => $this->resolveMenu($section->dataFor('desktop')),
                        'media' => $this->resolveMedia($section),
                    ];
                });

                $areas[$area] = [
                    'layout' => $layout,
                    'sections' => $sections,
                    // Area-level background image (footer design 10 Footer.png).
                    'bg_media' => ($bgId = $layout->setting('bg_media_id'))
                        ? Media::query()->find((int) $bgId)
                        : null,
                    // Footer right-side model photo, split out from the
                    // background artwork so it can be swapped independently.
                    'side_media' => ($sideId = $layout->setting('side_media_id'))
                        ? Media::query()->find((int) $sideId)
                        : null,
                ];
            }

            return $areas;
        });
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Render context (resolved menu + media map) for one section of ANY
     * area — used by Developer Mode to re-render a section in isolation.
     *
     * @return array{menu: ?object, media: array}
     */
    public function contextFor($section): array
    {
        return [
            'menu' => $this->resolveMenu($section->dataFor('desktop')),
            'media' => $this->resolveMedia($section),
        ];
    }

    private function resolveMenu(array $data): ?object
    {
        $menuId = $data['menu_id'] ?? null;

        return $menuId ? $this->menus->tree((int) $menuId) : null;
    }

    /**
     * Media referenced by any device payload, keyed by id. Recursively
     * harvests media_id and *_media_id (incl. repeater rows) — same
     * contract as PageRenderService::collectMedia().
     */
    private function resolveMedia($section): array
    {
        $ids = [];

        $harvest = function ($value, ?string $key) use (&$harvest, &$ids): void {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $harvest($v, is_string($k) ? $k : null);
                }

                return;
            }

            if ($key !== null && str_ends_with($key, 'media_id') && is_numeric($value)) {
                $ids[] = (int) $value;
            }
        };

        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            $harvest($section->dataFor($device), null);
        }

        $harvest((array) $section->style, null);

        return $ids === []
            ? []
            : Media::query()->whereIn('id', array_unique($ids))->get()->keyBy('id')->all();
    }
}
