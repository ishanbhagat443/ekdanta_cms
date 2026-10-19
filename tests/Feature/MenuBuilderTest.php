<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MenuBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        foreach (['menus.manage', 'catalog.manage'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    /* ── Menu CRUD ─────────────────────────────────────────────────── */

    public function test_menus_require_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/menus')->assertForbidden();
    }

    public function test_create_menu_with_location_and_unique_slug(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/menus', [
            'name' => 'Main Menu', 'location' => 'header', 'is_active' => 1,
        ])->assertRedirect();

        $menu = Menu::where('name', 'Main Menu')->firstOrFail();
        $this->assertSame('main-menu', $menu->slug);
        $this->assertSame('header', $menu->location);
        $this->assertTrue($menu->is_active);
        $this->assertSame('Header Menu', $menu->locationLabel());

        // Duplicate name rejected.
        $this->actingAs($admin)->post('/cms/menus', ['name' => 'Main Menu'])
            ->assertSessionHasErrors('name');
    }

    public function test_edit_page_renders_tree_items_without_blade_error(): void
    {
        // Regression: tree-item.blade.php passed a multi-line array literal to
        // @json(), which splits its argument on commas and truncated the
        // expression ("Unclosed '['") — the edit page 500'd on any menu item.
        $admin = $this->admin();
        $menu = Menu::create(['name' => 'Nav', 'slug' => 'nav', 'location' => 'header', 'is_active' => true]);
        $parent = MenuItem::create(['menu_id' => $menu->id, 'label' => 'Parent', 'link_type' => 'custom', 'url' => '/x', 'is_active' => true]);
        MenuItem::create(['menu_id' => $menu->id, 'parent_id' => $parent->id, 'label' => 'Child', 'link_type' => 'custom', 'url' => '/y', 'is_active' => true]);

        $this->actingAs($admin)->get("/cms/menus/{$menu->id}/edit")
            ->assertOk()
            ->assertSee('Parent')
            ->assertSee('Child')
            ->assertSee('gsm-item-edit', false); // edit buttons carry the data-item JSON
    }

    public function test_update_menu_settings(): void
    {
        $admin = $this->admin();
        $menu = Menu::create(['name' => 'Footer Links', 'slug' => 'footer-links']);

        $this->actingAs($admin)->put("/cms/menus/{$menu->id}", [
            'name' => 'Footer Links', 'location' => 'footer',
            'description' => 'Footer nav', 'is_active' => 0,
        ])->assertRedirect();

        $menu->refresh();
        $this->assertSame('footer', $menu->location);
        $this->assertSame('Footer nav', $menu->description);
        $this->assertFalse($menu->is_active);
    }

    /* ── Link types + resolvedUrl() ───────────────────────────────── */

    public function test_external_url_and_custom_link_resolve_verbatim(): void
    {
        $menu = Menu::create(['name' => 'M1']);
        $url = $menu->items()->create(['label' => 'Ext', 'link_type' => 'url', 'url' => 'https://example.com']);
        $custom = $menu->items()->create(['label' => 'Custom', 'link_type' => 'custom', 'url' => '/collection?tag=silk']);

        $this->assertSame('https://example.com', $url->resolvedUrl());
        $this->assertSame('/collection?tag=silk', $custom->resolvedUrl());
    }

    public function test_anchor_phone_email_resolve_with_correct_scheme(): void
    {
        $menu = Menu::create(['name' => 'M2']);
        $anchor = $menu->items()->create(['label' => 'Jump', 'link_type' => 'anchor', 'url' => '#contact-form']);
        $phone = $menu->items()->create(['label' => 'Call', 'link_type' => 'phone', 'url' => '+91 98765 43210']);
        $email = $menu->items()->create(['label' => 'Mail', 'link_type' => 'email', 'url' => 'info@globalsilkmills.com']);

        $this->assertSame('#contact-form', $anchor->resolvedUrl());
        $this->assertSame('tel:+919876543210', $phone->resolvedUrl());
        $this->assertSame('mailto:info@globalsilkmills.com', $email->resolvedUrl());
    }

    public function test_page_blog_and_blog_category_resolve_via_reference(): void
    {
        $menu = Menu::create(['name' => 'M3']);

        $page = Page::create(['title' => 'About', 'slug' => 'about-us', 'status' => Page::STATUS_PUBLISHED]);
        $post = Post::create(['title' => 'Silk Guide', 'slug' => 'silk-guide', 'status' => Post::STATUS_PUBLISHED, 'body' => 'x']);
        $blogCategory = BlogCategory::create(['name' => 'Fabrics', 'slug' => 'fabrics']);

        $pageItem = $menu->items()->create(['label' => 'About', 'link_type' => 'page', 'reference_id' => $page->id, 'reference_type' => Page::class]);
        $blogItem = $menu->items()->create(['label' => 'Guide', 'link_type' => 'blog', 'reference_id' => $post->id, 'reference_type' => Post::class]);
        $blogCatItem = $menu->items()->create(['label' => 'Fabrics', 'link_type' => 'blog_category', 'reference_id' => $blogCategory->id, 'reference_type' => BlogCategory::class]);

        $this->assertSame(url('/about-us'), $pageItem->resolvedUrl());
        $this->assertSame(url('/blog/silk-guide'), $blogItem->resolvedUrl());
        $this->assertSame(url('/blog?category=fabrics'), $blogCatItem->resolvedUrl());
    }

    public function test_home_page_reference_resolves_to_site_root(): void
    {
        $menu = Menu::create(['name' => 'M4']);
        $home = Page::create(['title' => 'Home', 'slug' => 'home', 'status' => Page::STATUS_PUBLISHED]);
        $item = $menu->items()->create(['label' => 'Home', 'link_type' => 'page', 'reference_id' => $home->id, 'reference_type' => Page::class]);

        $this->assertSame(url('/'), $item->resolvedUrl());
    }

    public function test_deleted_reference_falls_back_to_hash_not_500(): void
    {
        $menu = Menu::create(['name' => 'M5']);
        $item = $menu->items()->create(['label' => 'Gone', 'link_type' => 'page', 'reference_id' => 999999, 'reference_type' => Page::class]);

        $this->assertSame('#', $item->resolvedUrl());
    }

    /* ── Item CRUD w/ new fields via AJAX ──────────────────────────── */

    public function test_create_item_with_full_field_set(): void
    {
        $admin = $this->admin();
        $menu = Menu::create(['name' => 'Main Menu']);

        $response = $this->actingAs($admin)->postJson("/cms/menus/{$menu->id}/items", [
            'label' => 'New Arrivals', 'nav_label' => 'New!', 'link_type' => 'custom', 'url' => '/collection?filter=new',
            'icon' => 'fas fa-star', 'css_class' => 'text-danger', 'element_id' => 'nav-new',
            'badge_text' => 'Hot', 'badge_color' => 'danger', 'auth_only' => false, 'guest_only' => false,
            'target' => '_blank', 'is_active' => true,
        ])->assertCreated();

        $item = MenuItem::findOrFail($response->json('item.id'));
        $this->assertSame('New Arrivals', $item->label);
        $this->assertSame('New!', $item->nav_label);
        $this->assertSame('New!', $item->displayLabel());
        $this->assertSame('nav-new', $item->element_id);
        $this->assertSame('Hot', $item->badge_text);
        $this->assertSame('badge badge-danger', $item->badgeClass());
        $this->assertSame('_blank', $item->target);
    }

    public function test_reference_link_type_stores_reference_type_and_clears_url(): void
    {
        $admin = $this->admin();
        $menu = Menu::create(['name' => 'M6']);
        $page = Page::create(['title' => 'Kurtis', 'slug' => 'kurtis', 'status' => Page::STATUS_PUBLISHED]);

        $response = $this->actingAs($admin)->postJson("/cms/menus/{$menu->id}/items", [
            'label' => 'Kurtis', 'link_type' => 'page', 'reference_id' => $page->id, 'url' => 'ignored-value',
        ])->assertCreated();

        $item = MenuItem::findOrFail($response->json('item.id'));
        $this->assertSame(Page::class, $item->reference_type);
        $this->assertSame($page->id, $item->reference_id);
        $this->assertNull($item->url);
        $this->assertSame(url('/kurtis'), $item->resolvedUrl());
    }

    public function test_update_item_toggles_visibility_flags(): void
    {
        $admin = $this->admin();
        $menu = Menu::create(['name' => 'M7']);
        $item = $menu->items()->create(['label' => 'Account', 'link_type' => 'url', 'url' => '/account']);

        $this->actingAs($admin)->putJson("/cms/menu-items/{$item->id}", [
            'label' => 'Account', 'auth_only' => true, 'permission' => null,
        ])->assertOk();

        $this->assertTrue($item->fresh()->auth_only);
    }

    /* ── Mega menu columns ─────────────────────────────────────────── */

    public function test_mega_menu_with_image_html_and_cta_columns(): void
    {
        $menu = Menu::create(['name' => 'Collection Menu']);
        $media = Media::create(['disk' => 'public', 'path' => 'media/banner.jpg', 'filename' => 'banner.jpg', 'mime' => 'image/jpeg', 'type' => 'image', 'size' => 10]);

        $parent = $menu->items()->create(['label' => 'Collection', 'is_mega' => true, 'mega_columns' => 4]);

        $imageColumn = $menu->items()->create([
            'label' => 'Banner', 'parent_id' => $parent->id, 'column_type' => 'image', 'image_media_id' => $media->id,
        ]);
        $htmlColumn = $menu->items()->create([
            'label' => 'Promo', 'parent_id' => $parent->id, 'column_type' => 'html', 'custom_html' => '<p>Sale!</p>',
        ]);
        $ctaColumn = $menu->items()->create([
            'label' => 'Shop', 'parent_id' => $parent->id, 'column_type' => 'cta',
            'cta_label' => 'Shop Now', 'cta_url' => '/blogs', 'cta_style' => 'outline',
        ]);
        $this->assertSame('image', $imageColumn->column_type);
        $this->assertSame($media->id, $imageColumn->image_media_id);
        $this->assertSame('<p>Sale!</p>', $htmlColumn->custom_html);
        $this->assertSame('Shop Now', $ctaColumn->cta_label);
        $this->assertSame('outline', $ctaColumn->cta_style);
    }

    public function test_reference_search_endpoint_returns_matching_pages(): void
    {
        $admin = $this->admin();
        Page::create(['title' => 'Our Story', 'slug' => 'our-story', 'status' => Page::STATUS_PUBLISHED]);
        Page::create(['title' => 'Draft Page', 'slug' => 'draft-page', 'status' => Page::STATUS_DRAFT]);

        $response = $this->actingAs($admin)->getJson('/cms/menus/reference/page?q=Story')->assertOk();

        $results = $response->json('results');
        $this->assertCount(1, $results);
        $this->assertSame('Our Story', $results[0]['text']);
    }

    public function test_reference_search_rejects_unknown_type(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->getJson('/cms/menus/reference/bogus')->assertNotFound();
    }

    /* ── Permission / auth / guest visibility ─────────────────────── */

    public function test_permission_gated_item_hidden_from_users_without_it(): void
    {
        Permission::findOrCreate('reports.view', 'web');
        $menu = Menu::create(['name' => 'M8']);
        $item = $menu->items()->create(['label' => 'Reports', 'link_type' => 'url', 'url' => '/reports', 'permission' => 'reports.view']);

        $this->assertFalse($item->isVisibleToCurrentUser()); // guest

        $plainUser = User::factory()->create(['is_active' => true]);
        $this->actingAs($plainUser);
        $this->assertFalse($item->isVisibleToCurrentUser());

        $privilegedUser = User::factory()->create(['is_active' => true]);
        $privilegedUser->givePermissionTo('reports.view');
        $this->actingAs($privilegedUser);
        $this->assertTrue($item->fresh()->isVisibleToCurrentUser());
    }

    public function test_auth_only_and_guest_only_visibility(): void
    {
        $menu = Menu::create(['name' => 'M9']);
        $authItem = $menu->items()->create(['label' => 'My Account', 'link_type' => 'url', 'url' => '/account', 'auth_only' => true]);
        $guestItem = $menu->items()->create(['label' => 'Login', 'link_type' => 'url', 'url' => '/login', 'guest_only' => true]);

        // Guest: sees guest-only, not auth-only.
        $this->assertFalse($authItem->isVisibleToCurrentUser());
        $this->assertTrue($guestItem->isVisibleToCurrentUser());

        // Logged in: sees auth-only, not guest-only.
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);
        $this->assertTrue($authItem->fresh()->isVisibleToCurrentUser());
        $this->assertFalse($guestItem->fresh()->isVisibleToCurrentUser());
    }

    /* ── Device visibility ─────────────────────────────────────────── */

    public function test_device_visibility_classes(): void
    {
        $menu = Menu::create(['name' => 'M10']);
        $allDevices = $menu->items()->create(['label' => 'All', 'link_type' => 'url', 'url' => '/']);
        $desktopOnly = $menu->items()->create(['label' => 'Desktop', 'link_type' => 'url', 'url' => '/', 'visible_devices' => ['desktop']]);

        $this->assertSame('', $allDevices->deviceVisibilityClasses());
        $this->assertSame('d-none d-md-none d-lg-block gsm-t-none', $desktopOnly->deviceVisibilityClasses());
    }

    /* ── Frontend integration (existing HeaderBuilderTest already
         covers dropdown + basic mega rendering; this adds badge +
         permission-gated rendering on the live header). ───────────── */

    public function test_frontend_hides_permission_gated_and_shows_badge(): void
    {
        $this->seed(\Database\Seeders\HeaderSeeder::class);

        $menu = Menu::where('name', 'Main Menu')->firstOrFail();
        $menu->items()->create(['label' => 'Wholesale', 'link_type' => 'url', 'url' => '/wholesale', 'badge_text' => 'B2B', 'sort_order' => 99]);
        Permission::findOrCreate('wholesale.view', 'web');
        $menu->items()->create(['label' => 'Secret', 'link_type' => 'url', 'url' => '/secret', 'permission' => 'wholesale.view', 'sort_order' => 100]);

        app(\App\Services\LayoutService::class)->flushCache();

        $response = $this->get('/')->assertOk();
        $response->assertSee('Wholesale');
        $response->assertSee('B2B');
        $response->assertDontSee('Secret');
    }
}
