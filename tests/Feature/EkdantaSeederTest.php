<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GlobalBlock;
use App\Models\Layout;
use App\Models\Menu;
use App\Models\Page;
use App\Models\ThemePreset;
use App\Services\ThemeService;
use App\Support\PageRoles;
use App\Services\SettingsService;
use Database\Seeders\EkdantaSiteSeeder;
use Database\Seeders\HeaderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** `php artisan ekdanta:seed` sets up the site once and never overwrites what the admin changed. */
class EkdantaSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedSite(): void
    {
        $seeder = new EkdantaSiteSeeder;
        $seeder->business();
        $seeder->menus();
        $seeder->header();
    }

    public function test_it_replaces_the_starter_header_and_runs_again_without_duplicates(): void
    {
        $this->seed(HeaderSeeder::class);

        $this->seedSite();
        $this->seedSite();

        $header = Layout::query()->where('area', Layout::AREA_HEADER)->first();
        $this->assertSame(['logo', 'nav-menu', 'cta-button'], $header->sections()->orderBy('sort_order')->pluck('type')->all());
        $this->assertSame('pill', $header->headerVariant());
        $this->assertSame('overlay', $header->navStyle('mobile'));
        $this->assertSame(27, $header->sections()->where('type', 'logo')->first()->dataFor('mobile')['max_height']);
        $this->assertSame(5, Menu::query()->where('slug', 'ekdanta-main-menu')->first()->items()->count());
        $this->assertSame(7, Menu::query()->where('slug', 'ekdanta-home-page-menu')->first()->items()->count());
        $this->assertCount(3, business_phones());

        $this->get('/')->assertSee('gsm-header-pill', false)->assertSee('Become a big brand');
    }

    public function test_it_leaves_a_customised_header_and_filled_settings_alone(): void
    {
        $header = Layout::query()->create(['area' => Layout::AREA_HEADER, 'settings' => ['variant' => 'normal']]);
        $header->sections()->create(['type' => 'cta-button', 'name' => 'Call', 'sort_order' => 0, 'is_active' => true, 'data_desktop' => ['label' => 'Call us', 'url' => '/contact']]);
        app(SettingsService::class)->updateGroup('business', ['email' => 'owner@example.com']);

        $this->seedSite();

        $this->assertSame(['cta-button'], $header->sections()->pluck('type')->all());
        $this->assertSame('normal', $header->fresh()->headerVariant());
        $this->assertSame('owner@example.com', setting('business.email'));
        $this->assertSame('91', setting('business.country_code'));
    }

    public function test_office_hours_still_holding_the_placeholder_get_the_real_ones_and_typed_ones_stay(): void
    {
        $office = ['name' => 'Surat', 'address' => 'Bhatar', 'directions_url' => '', 'latitude' => '', 'longitude' => ''];
        app(SettingsService::class)->updateGroup('business', ['offices' => [$office + ['hours' => '[Add office hours]']]]);
        (new EkdantaSiteSeeder)->business();
        $this->assertSame('Mon–Sat, 10 am – 6:30 pm', business_offices()[0]['hours']);
        $this->assertSame('Bhatar', business_offices()[0]['address'], 'the rest of the office is kept');

        app(SettingsService::class)->updateGroup('business', ['offices' => [$office + ['hours' => 'Mon–Fri, 9–5']]]);
        (new EkdantaSiteSeeder)->business();
        $this->assertSame('Mon–Fri, 9–5', business_offices()[0]['hours']);
    }

    public function test_it_applies_the_theme_once_and_keeps_the_old_one_as_a_backup(): void
    {
        $seeder = new EkdantaSiteSeeder;
        $seeder->theme();

        $this->assertStringContainsString('--tk-ink: #0D1410', (string) app(ThemeService::class)->get('custom_code.css_variables'));
        $this->assertTrue(ThemePreset::query()->where('slug', 'ekdanta')->exists());
        $this->assertTrue(ThemePreset::query()->where('name', 'Before Ekdanta')->exists());

        // Edited afterwards: a second run keeps the edit.
        app(ThemeService::class)->updateGroup('colors', ['primary' => '#123456']);
        $seeder->theme();
        $this->assertSame('#123456', app(ThemeService::class)->get('colors.primary'));
        $this->assertSame(1, ThemePreset::query()->where('name', 'Before Ekdanta')->count());
    }

    public function test_with_pages_it_builds_every_page_once_around_one_synced_finale(): void
    {
        $kept = Page::create(['title' => 'Work', 'slug' => 'work', 'status' => Page::STATUS_PUBLISHED]);
        $kept->sections()->create(['type' => 'text-block', 'name' => 'Mine', 'sort_order' => 0, 'is_active' => true, 'data_desktop' => ['heading' => 'Mine']]);

        $seeder = new EkdantaSiteSeeder;
        $seeder->withPages = true;
        foreach (['collections', 'menus', 'finale', 'briefForm', 'pages', 'pages'] as $step) {
            $seeder->{$step}();
        }

        $finale = GlobalBlock::query()->where('slug', 'big-brand-finale')->sole();
        $home = Page::query()->where('slug', 'home')->sole();
        $this->assertSame(['ekdanta-home-hero', 'ekdanta-obstacles', 'ekdanta-work-showcase', 'ekdanta-reels-carousel', 'ekdanta-brand-shelves', 'ekdanta-services-panels', 'ekdanta-founders-story', 'ekdanta-finale'],
            $home->sections()->orderBy('sort_order')->pluck('type')->all());
        $this->assertSame(10, $finale->instances()->where('is_synced', true)->count(), 'every page but the one kept');
        $this->assertSame(['Mine'], $kept->sections()->pluck('name')->all(), 'a page with sections is left alone');
        $this->assertTrue($home->is(PageRoles::page('home')));
        $this->assertSame('page-not-found', PageRoles::page('not_found')->slug);
        $this->assertSame('post', Page::query()->where('slug', 'article-template')->sole()->setting('template_for'));
        $this->assertSame(Menu::query()->where('slug', 'ekdanta-blog-menu')->value('id'), Page::query()->where('slug', 'blog')->sole()->setting('header.menu_id'));

        $this->get('/')->assertOk()->assertSee('j-top', false);
        $this->get('/contact')->assertOk()->assertSee('c-brief', false);
        $this->get('/no-such-page')->assertNotFound()->assertSee('n-routes', false);
    }
}
