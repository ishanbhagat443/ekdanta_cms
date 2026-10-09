<?php

declare(strict_types=1);

use App\Models\Layout;
use App\Models\Section;
use App\Services\SectionTypes\SectionTypeRegistry;
use Illuminate\Database\Migrations\Migration;

/**
 * The header used to hard-code a search bar. It is now the Header Builder's
 * Search section, so a site keeps its search bar only if it has one: add it
 * where it is missing, in the "always-visible input" mode the old bar had.
 * Tagged, so down() removes only what this added.
 */
return new class extends Migration
{
    private const TAG = 'header-search-2026-10';

    public function up(): void
    {
        $search = app(SectionTypeRegistry::class)->find('search');

        Layout::query()->where('area', Layout::AREA_HEADER)->get()->each(function (Layout $layout) use ($search): void {
            if ($layout->sections()->where('type', 'search')->exists()) {
                return;
            }

            $layout->sections()->create([
                'type' => 'search',
                'name' => $search->label(),
                'sort_order' => (int) $layout->sections()->max('sort_order') + 1,
                'is_active' => true,
                'data_desktop' => array_replace($search->defaults(), ['display' => 'input']),
                'style' => ['migrated' => self::TAG],
            ]);
        });

        app(\App\Services\LayoutService::class)->flushCache();
    }

    public function down(): void
    {
        Section::query()->where('type', 'search')->where('style->migrated', self::TAG)->delete();
        app(\App\Services\LayoutService::class)->flushCache();
    }
};
