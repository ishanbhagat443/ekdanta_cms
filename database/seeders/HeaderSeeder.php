<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Layout;
use App\Models\Menu;
use App\Services\LayoutService;
use Illuminate\Database\Seeder;

class HeaderSeeder extends Seeder
{
    /**
     * Starter header: a main menu with Home + Blogs, a logo slot and the
     * nav. Idempotent — only seeds when the area has no sections yet.
     */
    public function run(): void
    {
        $this->seedMainMenu();
        $this->seedHeader();

        app(LayoutService::class)->flushCache();
    }

    private function seedMainMenu(): Menu
    {
        $menu = Menu::query()->firstOrCreate(
            ['name' => 'Main Menu'],
            ['slug' => 'main-menu', 'location' => 'header', 'is_active' => true],
        );

        if ($menu->items()->count() === 0) {
            $links = [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Blogs', 'url' => '/blogs'],
            ];

            foreach ($links as $order => $link) {
                $menu->items()->create([
                    'label' => $link['label'],
                    'link_type' => 'url',
                    'url' => $link['url'],
                    'sort_order' => $order,
                ]);
            }
        }

        return $menu;
    }

    private function seedHeader(): void
    {
        $header = Layout::query()->firstOrCreate(['area' => Layout::AREA_HEADER]);

        if ($header->sections()->count() > 0) {
            return;
        }

        $header->update([
            'settings' => [
                'sticky' => true,
                'container' => 'container',
                'show_topbar' => false,
            ],
        ]);

        $menu = Menu::query()->where('name', 'Main Menu')->first();

        $header->sections()->createMany([
            [
                'type' => 'logo',
                'name' => 'Site Logo',
                'sort_order' => 0,
                'data_desktop' => ['media_id' => null, 'alt' => config('app.name'), 'link' => '/', 'max_height' => 68],
            ],
            [
                'type' => 'nav-menu',
                'name' => 'Main Navigation',
                'sort_order' => 1,
                'data_desktop' => ['menu_id' => $menu?->id, 'alignment' => 'center'],
            ],
        ]);
    }
}
