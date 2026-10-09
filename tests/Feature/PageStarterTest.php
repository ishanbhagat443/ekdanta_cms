<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Page;
use App\Models\User;
use App\Services\PageStarterService;
use App\Support\SectionMotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Starter layouts and site kits: a new user edits a finished page instead of
 * facing a blank one.
 */
class PageStarterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (config('gsm.roles') as $role) {
            Role::findOrCreate($role, 'web');
        }

        Permission::findOrCreate('pages.manage', 'web');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function starters(): PageStarterService
    {
        return app(PageStarterService::class);
    }

    /* ── Definitions ─────────────────────────────────────────────── */

    public function test_every_starter_only_uses_section_types_that_exist(): void
    {
        $registry = app(\App\Services\SectionTypes\SectionTypeRegistry::class);

        foreach ($this->starters()->starters() as $key => $starter) {
            foreach ($starter['sections'] as $section) {
                $this->assertTrue(
                    $registry->has($section['type']),
                    "Starter [{$key}] uses unknown section type [{$section['type']}]."
                );
            }
        }
    }

    public function test_every_kit_references_starters_that_exist(): void
    {
        $starters = $this->starters()->starters();

        foreach ($this->starters()->kits() as $key => $kit) {
            foreach ($kit['pages'] as $spec) {
                $this->assertTrue(
                    $starters->has($spec['starter']),
                    "Kit [{$key}] references unknown starter [{$spec['starter']}]."
                );
            }
        }
    }

    public function test_the_brief_asks_for_at_least_eight_layouts(): void
    {
        $this->assertGreaterThanOrEqual(8, $this->starters()->starters()->count());
        $this->assertGreaterThanOrEqual(1, $this->starters()->kits()->count());
    }

    /* ── Applying one starter ────────────────────────────────────── */

    public function test_creating_a_page_from_a_starter_fills_it(): void
    {
        $this->actingAs($this->admin())
            ->post('/cms/pages', ['title' => 'Our Services', 'starter' => 'services'])
            ->assertRedirect();

        $page = Page::where('slug', 'our-services')->firstOrFail();

        $this->assertGreaterThan(3, $page->sections->count());
        $this->assertNotEmpty($page->sections->first()->data_desktop['heading'] ?? null);
    }

    public function test_a_blank_choice_leaves_the_page_empty(): void
    {
        $this->actingAs($this->admin())
            ->post('/cms/pages', ['title' => 'Empty One', 'starter' => 'blank'])
            ->assertRedirect();

        $this->assertCount(0, Page::where('slug', 'empty-one')->firstOrFail()->sections);
    }

    public function test_starter_sections_come_with_animation_that_covers_every_device(): void
    {
        $page = Page::create(['title' => 'Home', 'slug' => 'home-x', 'status' => Page::STATUS_DRAFT]);
        $this->starters()->apply($page, 'home');

        $motion = SectionMotion::effective($page->refresh()->sections->first());

        $this->assertSame('fade-up', $motion['desktop']['tracks']['enter']['preset']);
        $this->assertSame('fade-up', $motion['mobile']['tracks']['enter']['preset'], 'phones animate too');
    }

    public function test_a_starter_never_buries_existing_content(): void
    {
        $page = Page::create(['title' => 'Has Content', 'slug' => 'has-content', 'status' => Page::STATUS_DRAFT]);
        $page->sections()->create(['type' => 'hero', 'name' => 'Mine', 'sort_order' => 0, 'is_active' => true]);

        $this->starters()->apply($page->refresh(), 'services');

        $sections = $page->refresh()->sections;
        $this->assertCount(1, $sections);
        $this->assertSame('Mine', $sections->first()->name);
    }

    public function test_applying_a_starter_to_an_empty_page_from_the_builder(): void
    {
        $page = Page::create(['title' => 'Fill Me', 'slug' => 'fill-me', 'status' => Page::STATUS_DRAFT]);

        $this->actingAs($this->admin())
            ->post("/cms/pages/{$page->id}/starter", ['starter' => 'faq'])
            ->assertRedirect(route('admin.pages.edit', $page));

        $this->assertGreaterThan(1, $page->refresh()->sections->count());
    }

    public function test_the_builder_route_refuses_to_overwrite(): void
    {
        $page = Page::create(['title' => 'Busy', 'slug' => 'busy', 'status' => Page::STATUS_DRAFT]);
        $page->sections()->create(['type' => 'hero', 'name' => 'Mine', 'sort_order' => 0, 'is_active' => true]);

        $this->actingAs($this->admin())
            ->post("/cms/pages/{$page->id}/starter", ['starter' => 'faq'])
            ->assertSessionHas('error');

        $this->assertCount(1, $page->refresh()->sections);
    }

    /* ── Site kits ───────────────────────────────────────────────── */

    public function test_a_kit_creates_its_pages_and_menu_entries(): void
    {
        Menu::create(['name' => 'Main Menu', 'slug' => 'main-menu', 'location' => 'header', 'is_active' => true]);

        $this->actingAs($this->admin())
            ->post('/cms/pages/kits/small-business')
            ->assertRedirect(route('admin.pages.index'));

        foreach (['home', 'about-us', 'services', 'contact-us'] as $slug) {
            $page = Page::where('slug', $slug)->first();

            $this->assertNotNull($page, "Kit did not create [{$slug}].");
            $this->assertGreaterThan(0, $page->sections->count(), "[{$slug}] came out empty.");
            $this->assertSame(Page::STATUS_PUBLISHED, $page->status);
        }

        $labels = Menu::where('location', 'header')->first()->items()->pluck('label');

        $this->assertContains('About Us', $labels);
        $this->assertContains('Services', $labels);
    }

    public function test_a_kit_leaves_pages_that_already_have_content_alone(): void
    {
        Menu::create(['name' => 'Main Menu', 'slug' => 'main-menu', 'location' => 'header', 'is_active' => true]);

        $home = Page::create(['title' => 'Home', 'slug' => 'home', 'status' => Page::STATUS_PUBLISHED]);
        $home->sections()->create(['type' => 'hero', 'name' => 'My hero', 'sort_order' => 0, 'is_active' => true]);

        $this->actingAs($this->admin())->post('/cms/pages/kits/small-business');

        $sections = $home->refresh()->sections;
        $this->assertCount(1, $sections);
        $this->assertSame('My hero', $sections->first()->name);
    }

    public function test_running_a_kit_twice_does_not_duplicate_menu_items(): void
    {
        Menu::create(['name' => 'Main Menu', 'slug' => 'main-menu', 'location' => 'header', 'is_active' => true]);

        $this->actingAs($this->admin())->post('/cms/pages/kits/small-business');
        $first = Menu::where('location', 'header')->first()->items()->count();

        $this->actingAs($this->admin())->post('/cms/pages/kits/small-business');
        $second = Menu::where('location', 'header')->first()->items()->count();

        $this->assertSame($first, $second);
    }

    /**
     * A page in the bin still holds its slug. Adopting it would leave the
     * kit's page deleted — invisible on the site and in the Pages list.
     */
    public function test_a_kit_does_not_adopt_a_page_that_is_in_the_bin(): void
    {
        Menu::create(['name' => 'Main Menu', 'slug' => 'main-menu', 'location' => 'header', 'is_active' => true]);

        $binned = Page::create(['title' => 'About Us', 'slug' => 'about-us', 'status' => Page::STATUS_DRAFT]);
        $binned->delete();

        $this->actingAs($this->admin())->post('/cms/pages/kits/small-business');

        $live = Page::where('title', 'About Us')->get();

        $this->assertCount(1, $live, 'the kit should have created a live About Us');
        $this->assertGreaterThan(0, $live->first()->sections->count());
        $this->assertTrue($binned->refresh()->trashed(), 'the binned page stays binned');
    }

    public function test_a_kit_keeps_the_banner_of_a_page_it_fills(): void
    {
        Menu::create(['name' => 'Main Menu', 'slug' => 'main-menu', 'location' => 'header', 'is_active' => true]);

        $media = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/banner.jpg', 'filename' => 'banner.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        // An empty page the kit will fill, with a banner the user set up.
        Page::create([
            'title' => 'Home', 'slug' => 'home', 'status' => Page::STATUS_DRAFT,
            'show_banner' => true, 'banner_title' => 'Keep me', 'banner_media_id' => $media->id,
        ]);

        $this->actingAs($this->admin())->post('/cms/pages/kits/small-business');

        $home = Page::where('slug', 'home')->firstOrFail();

        $this->assertTrue((bool) $home->show_banner, 'the banner was switched off');
        $this->assertSame('Keep me', $home->banner_title);
        $this->assertSame($media->id, $home->banner_media_id);
        $this->assertGreaterThan(0, $home->sections->count(), 'and it was still filled');
    }

    public function test_menu_entries_follow_the_pages_that_were_actually_created(): void
    {
        Menu::create(['name' => 'Main Menu', 'slug' => 'main-menu', 'location' => 'header', 'is_active' => true]);

        // A page in the bin holds 'about-us', so the kit's page gets a
        // de-duplicated slug. The menu must point at the page that exists.
        $binned = Page::create(['title' => 'Old About', 'slug' => 'about-us', 'status' => Page::STATUS_DRAFT]);
        $binned->delete();

        $this->actingAs($this->admin())->post('/cms/pages/kits/small-business');

        $about = Page::where('title', 'About Us')->firstOrFail();
        $urls = Menu::where('location', 'header')->first()->items()->pluck('url');

        $this->assertContains('/'.$about->slug, $urls);
        $this->assertNotSame('about-us', $about->slug, 'the slug was taken, so it should differ');
    }

    public function test_an_unknown_starter_is_rejected_rather_than_silently_ignored(): void
    {
        $this->actingAs($this->admin())
            ->post('/cms/pages', ['title' => 'Bad Starter', 'starter' => 'no-such-layout'])
            ->assertSessionHasErrors('starter');

        $this->assertNull(Page::where('slug', 'bad-starter')->first());
    }

    public function test_a_kit_does_not_publish_a_page_the_user_left_as_a_draft(): void
    {
        Menu::create(['name' => 'Main Menu', 'slug' => 'main-menu', 'location' => 'header', 'is_active' => true]);

        // An empty draft: the user wants it filled, not put live.
        Page::create(['title' => 'Home', 'slug' => 'home', 'status' => Page::STATUS_DRAFT]);

        $this->actingAs($this->admin())->post('/cms/pages/kits/small-business');

        $home = Page::where('slug', 'home')->firstOrFail();

        $this->assertSame(Page::STATUS_DRAFT, $home->status, 'the draft was published without being asked');
        $this->assertGreaterThan(0, $home->sections->count(), 'but it was still filled');

        // A page the kit created itself is published, because a kit exists to
        // produce a working site.
        $this->assertSame(Page::STATUS_PUBLISHED, Page::where('slug', 'services')->firstOrFail()->status);
    }

    public function test_an_unknown_kit_is_reported_not_fatal(): void
    {
        $this->actingAs($this->admin())
            ->post('/cms/pages/kits/does-not-exist')
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    /* ── Access control ──────────────────────────────────────────── */

    public function test_starters_require_the_pages_permission(): void
    {
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole(config('gsm.roles.viewer'));

        $page = Page::create(['title' => 'X', 'slug' => 'x', 'status' => Page::STATUS_DRAFT]);

        $this->actingAs($viewer)->post('/cms/pages/kits/small-business')->assertForbidden();
        $this->actingAs($viewer)->post("/cms/pages/{$page->id}/starter", ['starter' => 'faq'])->assertForbidden();
    }
}
