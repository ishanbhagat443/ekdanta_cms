<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use App\Services\PageRenderService;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Blank published Home page — the site needs one row for "/" to render.
     * Sections are added from Admin → Pages → Home.
     * Idempotent: skipped when a home page already exists.
     */
    public function run(): void
    {
        if (Page::withTrashed()->where('slug', 'home')->exists()) {
            return;
        }

        $home = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'status' => Page::STATUS_PUBLISHED,
            'show_banner' => false,
            'published_at' => now(),
        ]);

        app(PageRenderService::class)->flushCache($home);
    }
}
