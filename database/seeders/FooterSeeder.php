<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Layout;
use App\Models\Menu;
use App\Services\LayoutService;
use Illuminate\Database\Seeder;

class FooterSeeder extends Seeder
{
    /**
     * Starter footer: about + links + contact + copyright bar.
     * Idempotent — only seeds when the footer has no sections yet.
     */
    public function run(): void
    {
        $footer = Layout::query()->firstOrCreate(['area' => Layout::AREA_FOOTER]);

        if ($footer->sections()->count() > 0) {
            return;
        }

        $footer->update([
            'settings' => ['bg_color' => '#101828', 'text_color' => '#e8e8ef', 'container' => 'container'],
        ]);

        $menu = Menu::query()->where('name', 'Main Menu')->first();

        $footer->sections()->createMany([
            [
                'type' => 'footer-about',
                'name' => 'About + Logo',
                'sort_order' => 0,
                'data_desktop' => [
                    'media_id' => null,
                    'logo_max_height' => 64,
                    'text' => null,
                ],
                'style' => ['width' => ['desktop' => 4, 'tablet' => 6, 'mobile' => 12]],
            ],
            [
                'type' => 'footer-links',
                'name' => 'Useful Links',
                'sort_order' => 1,
                'data_desktop' => [
                    'title' => 'Useful Links',
                    'source' => 'menu',
                    'menu_id' => $menu?->id,
                    'links' => [],
                ],
                'style' => ['width' => ['desktop' => 2, 'tablet' => 6, 'mobile' => 12]],
            ],
            [
                'type' => 'footer-contact',
                'name' => 'Contact Details',
                'sort_order' => 2,
                'data_desktop' => [
                    'title' => 'Contact Us',
                    'show_address' => true,
                    'show_email' => true,
                    'show_phone' => true,
                    'show_whatsapp' => true,
                    'show_hours' => false,
                    'address_override' => null,
                ],
                'style' => ['width' => ['desktop' => 3, 'tablet' => 6, 'mobile' => 12]],
            ],
            [
                'type' => 'social-icons',
                'name' => 'Follow Us',
                'sort_order' => 3,
                'data_desktop' => ['title' => 'Follow Us', 'style' => 'circle'],
                'style' => ['width' => ['desktop' => 12, 'tablet' => 12, 'mobile' => 12]],
            ],
            [
                'type' => 'copyright',
                'name' => 'Copyright Bar',
                'sort_order' => 4,
                'data_desktop' => [
                    'text' => 'Copyright © {year} '.config('app.name').'. All rights reserved.',
                ],
            ],
        ]);

        app(LayoutService::class)->flushCache();
    }
}
