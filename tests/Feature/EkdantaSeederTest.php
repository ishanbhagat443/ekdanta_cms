<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Layout;
use App\Models\Menu;
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
}
