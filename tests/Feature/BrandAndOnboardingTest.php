<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Services\OnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The two screens a new user meets first: the setup checklist on the
 * dashboard, and Brand.
 */
class BrandAndOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (config('gsm.roles') as $role) {
            Role::findOrCreate($role, 'web');
        }

        foreach (['theme.manage', 'pages.manage', 'settings.manage'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    /* ── Onboarding ──────────────────────────────────────────────── */

    public function test_a_fresh_install_shows_every_step_as_outstanding(): void
    {
        $onboarding = app(OnboardingService::class);

        $this->assertSame($onboarding->steps()->count(), $onboarding->remaining());
        $this->assertFalse($onboarding->isComplete());
    }

    public function test_steps_tick_themselves_off_from_real_state(): void
    {
        $onboarding = app(OnboardingService::class);

        $this->assertFalse($onboarding->steps()->firstWhere('key', 'page')['done']);

        $page = Page::create(['title' => 'Home', 'slug' => 'home', 'status' => Page::STATUS_PUBLISHED]);
        $page->sections()->create(['type' => 'hero', 'name' => 'Hero', 'sort_order' => 0, 'is_active' => true]);

        $this->assertTrue(app(OnboardingService::class)->steps()->firstWhere('key', 'page')['done']);
        $this->assertTrue(app(OnboardingService::class)->steps()->firstWhere('key', 'publish')['done']);
    }

    public function test_the_checklist_shows_on_the_dashboard_until_it_is_finished(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/cms/dashboard')
            ->assertOk()
            ->assertSee('Set up your site')
            ->assertSee('Add your logo');

        // Satisfy every step, then it should disappear on its own.
        Setting::create(['group' => 'branding', 'key' => 'logo_dark', 'value' => 'media/logo.png', 'type' => 'image']);
        Setting::create(['group' => 'business', 'key' => 'email', 'value' => 'hi@example.com', 'type' => 'email']);
        app(\App\Services\SettingsService::class)->flushCache();

        \App\Models\ThemeSetting::create(['group' => 'colors', 'key' => 'primary', 'value' => '#123456']);

        $page = Page::create(['title' => 'Home', 'slug' => 'home', 'status' => Page::STATUS_PUBLISHED]);
        $page->sections()->create(['type' => 'hero', 'name' => 'Hero', 'sort_order' => 0, 'is_active' => true]);

        $menu = Menu::create(['name' => 'Main Menu', 'slug' => 'main-menu', 'location' => 'header', 'is_active' => true]);
        $menu->items()->create(['label' => 'Home', 'link_type' => 'url', 'url' => '/', 'sort_order' => 0]);

        $this->actingAs($admin)->get('/cms/dashboard')
            ->assertOk()
            ->assertDontSee('Set up your site')
            ->assertSee('Your site is set up');
    }

    public function test_the_dashboard_counts_what_is_there(): void
    {
        Page::create(['title' => 'A', 'slug' => 'a', 'status' => Page::STATUS_DRAFT]);
        Page::create(['title' => 'B', 'slug' => 'b', 'status' => Page::STATUS_DRAFT]);

        $counts = app(OnboardingService::class)->counts();

        $this->assertSame(2, $counts['pages']);
        $this->assertSame(0, $counts['posts']);
        $this->assertArrayHasKey('enquiries', $counts);
    }

    /* ── Brand ───────────────────────────────────────────────────── */

    public function test_brand_summarises_the_site_look(): void
    {
        $html = $this->actingAs($this->admin())->get('/cms/brand')->assertOk()->getContent();

        $this->assertStringContainsString('Colours', $html);
        $this->assertStringContainsString('Fonts', $html);
        $this->assertStringContainsString('Buttons', $html);

        // Each card links to the screen that actually owns the setting.
        $this->assertStringContainsString(route('admin.settings.edit', 'branding'), $html);
        $this->assertStringContainsString(route('admin.theme.index'), $html);
        $this->assertStringContainsString(route('admin.button-styles.index'), $html);
    }

    public function test_brand_says_plainly_when_there_is_no_logo(): void
    {
        $this->actingAs($this->admin())->get('/cms/brand')
            ->assertOk()
            ->assertSee('No logo yet', false)
            ->assertSee('Add your logo');
    }

    public function test_brand_shows_the_logo_once_one_is_set(): void
    {
        Setting::create(['group' => 'branding', 'key' => 'logo_dark', 'value' => 'media/logo.png', 'type' => 'image']);
        app(\App\Services\SettingsService::class)->flushCache();

        $this->actingAs($this->admin())->get('/cms/brand')
            ->assertOk()
            ->assertSee('media/logo.png', false)
            ->assertSee('Change logo');
    }

    public function test_brand_needs_the_theme_permission(): void
    {
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole(config('gsm.roles.viewer'));

        $this->actingAs($viewer)->get('/cms/brand')->assertForbidden();
    }

    public function test_brand_appears_in_the_sidebar(): void
    {
        $html = $this->actingAs($this->admin())->get('/cms/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('cms/brand', $html);
    }
}
