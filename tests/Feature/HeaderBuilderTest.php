<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Layout;
use App\Models\Menu;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HeaderBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        foreach (['builder.header', 'menus.manage'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function headerLayout(): Layout
    {
        return Layout::query()->firstOrCreate(['area' => Layout::AREA_HEADER]);
    }

    /* ── Access ─────────────────────────────────────────────────── */

    public function test_header_builder_requires_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/header-builder')->assertForbidden();
        $this->actingAs($this->admin())->get('/cms/header-builder')->assertOk();
    }

    /* ── Sections ───────────────────────────────────────────────── */

    public function test_add_section_with_defaults(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/cms/sections', ['area' => 'header', 'type' => 'cta-button'])
            ->assertCreated();

        $section = Section::firstOrFail();
        $this->assertSame('cta-button', $section->type);
        $this->assertSame('gradient', $section->data_desktop['style']);
    }

    public function test_section_type_must_be_allowed_in_area(): void
    {
        // top-bar is a topbar-only block
        $this->actingAs($this->admin())
            ->postJson('/cms/sections', ['area' => 'header', 'type' => 'top-bar'])
            ->assertUnprocessable();
    }

    public function test_desktop_payload_is_validated_against_schema(): void
    {
        $this->actingAs($this->admin())->postJson('/cms/sections', ['area' => 'header', 'type' => 'cta-button']);
        $section = Section::firstOrFail();

        // label required for cta-button
        $this->actingAs($this->admin())
            ->putJson("/cms/sections/{$section->id}", [
                'device' => 'desktop',
                'data' => ['label' => '', 'url' => '/x'],
            ])
            ->assertUnprocessable();
    }

    public function test_device_override_inheritance(): void
    {
        $this->actingAs($this->admin())->postJson('/cms/sections', ['area' => 'header', 'type' => 'cta-button']);
        $section = Section::firstOrFail();

        $this->actingAs($this->admin())->putJson("/cms/sections/{$section->id}", [
            'device' => 'desktop',
            'data' => ['label' => 'Contact Us', 'url' => '/contact-us', 'style' => 'gradient'],
        ])->assertOk();

        // Mobile overrides only the label; url stays inherited.
        $this->actingAs($this->admin())->putJson("/cms/sections/{$section->id}", [
            'device' => 'mobile',
            'data' => ['label' => 'Call', 'url' => ''],
        ])->assertOk();

        $section->refresh();
        $this->assertSame(['label' => 'Call'], $section->data_mobile);
        $this->assertSame('Call', $section->dataFor('mobile')['label']);
        $this->assertSame('/contact-us', $section->dataFor('mobile')['url']); // inherited
        $this->assertSame('Contact Us', $section->dataFor('desktop')['label']);
    }

    public function test_meta_only_save_does_not_wipe_payload(): void
    {
        $this->actingAs($this->admin())->postJson('/cms/sections', ['area' => 'header', 'type' => 'cta-button']);
        $section = Section::firstOrFail();

        $this->actingAs($this->admin())->putJson("/cms/sections/{$section->id}", [
            'device' => 'desktop',
            'data' => ['label' => 'Keep Me', 'url' => '/keep', 'style' => 'gradient'],
        ]);

        // Toggle active with data:null — payload must survive.
        $this->actingAs($this->admin())->putJson("/cms/sections/{$section->id}", [
            'device' => 'desktop',
            'data' => null,
            'is_active' => false,
        ])->assertOk();

        $section->refresh();
        $this->assertFalse($section->is_active);
        $this->assertSame('Keep Me', $section->data_desktop['label']);
    }

    public function test_clone_reorder_delete(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->postJson('/cms/sections', ['area' => 'header', 'type' => 'logo']);
        $this->actingAs($admin)->postJson('/cms/sections', ['area' => 'header', 'type' => 'cta-button']);

        [$logo, $cta] = Section::orderBy('sort_order')->get();

        // Clone
        $this->actingAs($admin)->postJson("/cms/sections/{$cta->id}/clone")->assertCreated();
        $this->assertSame(3, Section::count());
        $this->assertStringContainsString('(copy)', Section::orderByDesc('sort_order')->first()->name);

        // Reorder: cta first
        $this->actingAs($admin)->postJson('/cms/sections/reorder', [
            'area' => 'header',
            'ids' => [$cta->id, $logo->id],
        ])->assertOk();

        $this->assertSame(0, $cta->fresh()->sort_order);
        $this->assertSame(1, $logo->fresh()->sort_order);

        // Delete
        $this->actingAs($admin)->deleteJson("/cms/sections/{$logo->id}")->assertOk();
        $this->assertDatabaseMissing('sections', ['id' => $logo->id]);
    }

    public function test_layout_settings_and_custom_css_js(): void
    {
        $this->actingAs($this->admin())->put('/cms/header-builder', [
            'sticky' => '1',
            'container' => 'container-fluid',
            'show_topbar' => '0',
            'custom_css' => '.gsm-header { border-bottom: 2px solid red; }',
            'custom_js' => 'console.log("header");',
        ])->assertRedirect();

        $layout = $this->headerLayout()->fresh();
        $this->assertTrue($layout->setting('sticky'));
        $this->assertSame('container-fluid', $layout->setting('container'));
        $this->assertFalse($layout->setting('show_topbar'));
        $this->assertStringContainsString('red', $layout->custom_css);
    }

    /* ── Menus ──────────────────────────────────────────────────── */

    public function test_menu_and_item_crud_with_nesting(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/menus', ['name' => 'Main Menu'])->assertRedirect();
        $menu = Menu::firstOrFail();

        // Root item
        $this->actingAs($admin)->postJson("/cms/menus/{$menu->id}/items", [
            'label' => 'Collection', 'url' => '/collection',
        ])->assertCreated();

        $root = $menu->items()->firstOrFail();

        // Child (dropdown)
        $this->actingAs($admin)->postJson("/cms/menus/{$menu->id}/items", [
            'label' => 'Women Wear', 'url' => '/collection?category=women', 'parent_id' => $root->id,
        ])->assertCreated();

        // Grandchild (3rd level ok)
        $child = $menu->items()->where('parent_id', $root->id)->firstOrFail();
        $this->actingAs($admin)->postJson("/cms/menus/{$menu->id}/items", [
            'label' => 'Kurti', 'url' => '/collection?type=kurti', 'parent_id' => $child->id,
        ])->assertCreated();

        // 4th level rejected
        $grandchild = $menu->items()->where('parent_id', $child->id)->firstOrFail();
        $this->actingAs($admin)->postJson("/cms/menus/{$menu->id}/items", [
            'label' => 'Too Deep', 'parent_id' => $grandchild->id,
        ])->assertUnprocessable();

        // Update: mark mega
        $this->actingAs($admin)->putJson("/cms/menu-items/{$root->id}", [
            'label' => 'Collection', 'is_mega' => true,
        ])->assertOk();
        $this->assertTrue($root->fresh()->is_mega);

        // Tree reorder
        $this->actingAs($admin)->postJson("/cms/menus/{$menu->id}/tree", [
            'tree' => [['id' => $root->id, 'children' => [['id' => $child->id, 'children' => []]]]],
        ])->assertOk();

        // Delete cascades
        $this->actingAs($admin)->deleteJson("/cms/menu-items/{$root->id}")->assertOk();
        $this->assertSame(0, $menu->items()->count());
    }

    /* ── Frontend rendering ─────────────────────────────────────── */

    private function seedFrontendHeader(): void
    {
        $this->seed(\Database\Seeders\HeaderSeeder::class);

        // The starter seeder ships no top bar; these tests exercise it, so
        // build one here.
        \App\Models\Layout::query()->firstOrCreate(['area' => \App\Models\Layout::AREA_TOPBAR])
            ->sections()->create([
                'type' => 'top-bar',
                'name' => 'Announcement strip',
                'sort_order' => 0,
                'data_desktop' => [
                    'left_text' => 'FREE SHIPPING ACROSS INDIA',
                    'left_link_label' => 'Read More',
                    'left_link_url' => '/blogs',
                    'show_email' => true,
                    'show_phone' => true,
                ],
            ]);

        \App\Models\Layout::query()->where('area', \App\Models\Layout::AREA_HEADER)->firstOrFail()
            ->update(['settings' => ['sticky' => true, 'container' => 'container', 'show_topbar' => true]]);

        app(\App\Services\LayoutService::class)->flushCache();
    }

    public function test_overlay_header_renders_transparent_state_with_light_logo(): void
    {
        $admin = $this->admin();

        $dark = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/logo.png', 'filename' => 'logo.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);
        $light = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/logo-light.png', 'filename' => 'logo-light.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);

        $header = $this->headerLayout();
        $header->sections()->create([
            'type' => 'logo', 'sort_order' => 0,
            'data_desktop' => ['media_id' => $dark->id, 'light_media_id' => $light->id, 'link' => '/', 'alt' => 'GSM', 'max_height' => 56],
        ]);

        $this->actingAs($admin)->put('/cms/header-builder', [
            'overlay_hero' => 1,
            'transparent_color' => 'transparent',
            'scrolled_color' => '#ffffff',
            'blur' => 8,
            'scrolled_shadow' => 1,
            'menu_color' => '#ffffff',
            'menu_hover_color' => '#f7941d',
            'container' => 'container',
        ])->assertRedirect();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-header-overlay', $html);
        $this->assertStringContainsString('gsm-header-shadow-on-scroll', $html);
        $this->assertStringContainsString('--gsm-header-scrolled-bg: #ffffff;', $html);
        $this->assertStringContainsString('--gsm-header-blur: 8px;', $html);
        $this->assertStringContainsString('--gsm-header-menu: #ffffff;', $html);
        $this->assertStringContainsString('gsm-logo-light', $html);
        $this->assertStringContainsString($light->url, $html);
        $this->assertStringContainsString('gsm-logo-dark', $html);

        // Toggle off → classic solid header, no overlay vars.
        $this->actingAs($admin)->put('/cms/header-builder', ['overlay_hero' => 0, 'container' => 'container'])->assertRedirect();
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('gsm-header-overlay', $html);
        $this->assertStringContainsString('bg-white shadow-sm', $html);
    }

    public function test_frontend_renders_dynamic_header(): void
    {
        $this->seedFrontendHeader();

        $response = $this->get('/')->assertOk();

        // Top bar section
        $response->assertSee('FREE SHIPPING ACROSS INDIA');
        $response->assertSee('Read More');

        // Menu items from the Menus module
        foreach (['Home', 'Blogs'] as $label) {
            $response->assertSee($label);
        }

        // Sticky header from the seeded layout settings.
        $response->assertSee('sticky-top', false);
    }

    public function test_frontend_header_reflects_admin_changes(): void
    {
        $this->seedFrontendHeader();
        $admin = $this->admin();

        // Change a rendered header section + add custom CSS. (Uses the top
        // bar: the CTA button section this used to edit is no longer rendered
        // in the header — Contact Us lives in the menu dropdown.)
        $topBar = Section::where('type', 'top-bar')->firstOrFail();
        $this->actingAs($admin)->putJson("/cms/sections/{$topBar->id}", [
            'device' => 'desktop',
            'data' => ['left_text' => 'WHOLESALE ENQUIRIES OPEN', 'left_link_label' => 'Get Quote', 'left_link_url' => '/enquiry'],
        ]);
        $this->actingAs($admin)->put('/cms/header-builder', [
            'sticky' => '0',
            'custom_css' => '.gsm-header { background: yellow; }',
        ]);

        $response = $this->get('/')->assertOk();
        $response->assertSee('WHOLESALE ENQUIRIES OPEN');
        $response->assertSee('Get Quote');
        $response->assertSee('/enquiry');
        $response->assertDontSee('sticky-top');
        $response->assertSee('background: yellow', false);
    }

    public function test_disabled_section_and_hidden_topbar_not_rendered(): void
    {
        $this->seedFrontendHeader();
        $admin = $this->admin();

        // Disable the topbar strip section
        $topbarSection = Section::where('type', 'top-bar')->firstOrFail();
        $this->actingAs($admin)->putJson("/cms/sections/{$topbarSection->id}", [
            'device' => 'desktop',
            'data' => null,
            'is_active' => false,
        ]);

        $this->get('/')->assertOk()->assertDontSee('FREE SHIPPING');
    }

    public function test_device_visibility_classes_render(): void
    {
        $this->seedFrontendHeader();
        $admin = $this->admin();

        // Uses the logo section: it renders in the header at every breakpoint
        // (the CTA button section this used to target is no longer rendered).
        $logo = Section::where('type', 'logo')->firstOrFail();
        $this->actingAs($admin)->putJson("/cms/sections/{$logo->id}", [
            'device' => 'desktop',
            'data' => null,
            'style' => ['visible' => ['desktop' => true, 'tablet' => true, 'mobile' => false]],
        ])->assertOk();

        $this->get('/')->assertOk()->assertSee('d-md-block', false);
    }

    public function test_mega_menu_renders_columns(): void
    {
        $this->seedFrontendHeader();

        $menu = Menu::where('name', 'Main Menu')->firstOrFail();
        $blogs = $menu->items()->where('label', 'Blogs')->firstOrFail();
        $blogs->update(['is_mega' => true]);

        $column = $menu->items()->create([
            'label' => 'Women Wear', 'parent_id' => $blogs->id, 'sort_order' => 0,
        ]);
        $menu->items()->create([
            'label' => 'Plazzo Suits', 'url' => '/blogs?tag=plazzo',
            'parent_id' => $column->id, 'sort_order' => 0,
        ]);

        app(\App\Services\LayoutService::class)->flushCache();

        $response = $this->get('/')->assertOk();
        $response->assertSee('gsm-mega');
        $response->assertSee('Women Wear');
        $response->assertSee('Plazzo Suits');
    }
}
