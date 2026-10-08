<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Layout;
use App\Models\Section;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FooterBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        Role::findOrCreate(config('gsm.roles.marketing'), 'web');
        foreach (['builder.header', 'builder.footer'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    /** User holding ONLY builder.footer — must not touch header areas. */
    private function footerOnlyUser(): User
    {
        $role = Role::findOrCreate('Footer Editor', 'web');
        $role->givePermissionTo('builder.footer');

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('Footer Editor');

        return $user;
    }

    private function seedFooter(): void
    {
        $this->seed(\Database\Seeders\SettingSeeder::class);
        $this->seed(\Database\Seeders\HeaderSeeder::class);
        $this->seed(\Database\Seeders\FooterSeeder::class);
    }

    /* ── Access & area gates ────────────────────────────────────── */

    public function test_footer_builder_requires_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/footer-builder')->assertForbidden();
        $this->actingAs($this->footerOnlyUser())->get('/cms/footer-builder')->assertOk();
    }

    public function test_footer_permission_does_not_grant_header_area(): void
    {
        $user = $this->footerOnlyUser();

        // Can add to footer…
        $this->actingAs($user)
            ->postJson('/cms/sections', ['area' => 'footer', 'type' => 'footer-links'])
            ->assertCreated();

        // …but not to header.
        $this->actingAs($user)
            ->postJson('/cms/sections', ['area' => 'header', 'type' => 'cta-button'])
            ->assertForbidden();

        // And cannot edit a header-owned section.
        $header = Layout::query()->firstOrCreate(['area' => Layout::AREA_HEADER]);
        $headerSection = $header->sections()->create([
            'type' => 'cta-button', 'sort_order' => 0,
            'data_desktop' => ['label' => 'X', 'url' => '/x', 'style' => 'gradient'],
        ]);

        $this->actingAs($user)
            ->putJson("/cms/sections/{$headerSection->id}", [
                'device' => 'desktop', 'data' => null, 'is_active' => false,
            ])
            ->assertForbidden();
    }

    /* ── Widgets & layout ───────────────────────────────────────── */

    public function test_footer_widgets_only_allowed_in_footer(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/cms/sections', ['area' => 'header', 'type' => 'footer-about'])
            ->assertUnprocessable();
    }

    public function test_column_width_style_saved_and_rendered(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/cms/sections', ['area' => 'footer', 'type' => 'footer-links']);
        $section = Section::firstOrFail();

        $this->actingAs($admin)->putJson("/cms/sections/{$section->id}", [
            'device' => 'desktop',
            'data' => null,
            'style' => ['width' => ['desktop' => 4, 'tablet' => 6, 'mobile' => 12]],
        ])->assertOk();

        $this->assertSame('col-12 col-md-6 col-lg-4', $section->fresh()->widthClasses());
    }

    public function test_footer_settings_update(): void
    {
        $this->actingAs($this->admin())->put('/cms/footer-builder', [
            'bg_color' => '#222222',
            'text_color' => '#ffffff',
            'container' => 'container-fluid',
            'custom_css' => '.gsm-footer { letter-spacing: 1px; }',
        ])->assertRedirect();

        $footer = Layout::query()->where('area', 'footer')->firstOrFail();
        $this->assertSame('#222222', $footer->setting('bg_color'));
        $this->assertStringContainsString('letter-spacing', $footer->custom_css);
    }

    /* ── Frontend rendering ─────────────────────────────────────── */

    public function test_frontend_renders_seeded_footer(): void
    {
        $this->seedFooter();

        $response = $this->get('/')->assertOk();

        // Links column from Main Menu
        $response->assertSee('Useful Links');
        // Contact column heading
        $response->assertSee('Contact Us');
        // Copyright with {year} replaced
        $response->assertSee('Copyright © '.date('Y').' '.config('app.name'));
        $response->assertDontSee('{year}');
        // Column width classes present
        $response->assertSee('col-12 col-md-6 col-lg-4', false);
    }

    public function test_footer_reflects_admin_changes(): void
    {
        $this->seedFooter();
        $admin = $this->admin();

        $links = Section::where('type', 'footer-links')->firstOrFail();

        // Switch to custom links: Policies column
        $this->actingAs($admin)->putJson("/cms/sections/{$links->id}", [
            'device' => 'desktop',
            'data' => [
                'title' => 'Policies',
                'source' => 'custom',
                'menu_id' => null,
                'links' => [
                    ['label' => 'Privacy Policy', 'url' => '/privacy'],
                    ['label' => 'Refund Policy', 'url' => '/refunds'],
                ],
            ],
        ])->assertOk();

        $response = $this->get('/')->assertOk();
        $response->assertSee('Policies');
        $response->assertSee('Privacy Policy');
        $response->assertSee('/refunds');
        $response->assertDontSee('Useful Links');
    }

    public function test_hidden_widget_gets_visibility_classes(): void
    {
        $this->seedFooter();
        $admin = $this->admin();

        $newsletter = Layout::query()->where('area', 'footer')->firstOrFail()
            ->sections()->create(['type' => 'newsletter', 'name' => 'Newsletter', 'sort_order' => 9]);

        $this->actingAs($admin)->putJson("/cms/sections/{$newsletter->id}", [
            'device' => 'desktop',
            'data' => null,
            'style' => ['visible' => ['desktop' => true, 'tablet' => true, 'mobile' => false]],
        ])->assertOk();

        $this->get('/')->assertOk()->assertSee('d-none', false);
    }

    /* ── Newsletter ─────────────────────────────────────────────── */

    public function test_newsletter_subscribe_stores_and_dedupes(): void
    {
        $this->seedFooter();

        $this->post('/newsletter', ['email' => 'Buyer@Example.com'])
            ->assertRedirect()
            ->assertSessionHas('newsletter_success');

        $this->assertDatabaseHas('subscribers', ['email' => 'buyer@example.com', 'status' => 'active']);

        // Duplicate does not create a second row
        $this->post('/newsletter', ['email' => 'buyer@example.com']);
        $this->assertSame(1, Subscriber::count());
    }

    public function test_newsletter_rejects_invalid_email_and_honeypot(): void
    {
        $this->post('/newsletter', ['email' => 'not-an-email'])->assertSessionHasErrors('email');

        // Honeypot filled → silently dropped
        $this->post('/newsletter', ['email' => 'bot@example.com', 'website' => 'http://spam'])
            ->assertRedirect();
        $this->assertSame(0, Subscriber::count());
    }

    /* ── Design 09/10: Instagram strip + footer background ──────── */

    public function test_instagram_strip_renders_above_footer_with_photos(): void
    {
        $this->seedFooter();

        $photo = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/insta.jpg', 'filename' => 'insta.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $insta = Layout::query()->where('area', 'footer')->firstOrFail()->sections()->create([
            'type' => 'instagram-feed',
            'name' => 'Follow Us On Instagram',
            'sort_order' => 9,
            'data_desktop' => [
                'heading' => 'Follow Us On Instagram',
                'handle' => '@studio',
                'profile_url' => 'https://www.instagram.com/studio',
                'images' => array_fill(0, 6, ['image_media_id' => $photo->id, 'url' => null]),
                'bg_media_id' => null,
            ],
        ]);
        app(\App\Services\LayoutService::class)->flushCache();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Follow Us On Instagram', $html);
        $this->assertStringContainsString('@studio', $html);
        $this->assertStringContainsString('https://www.instagram.com/studio', $html);
        $this->assertSame(6, substr_count($html, 'gsm-insta-item'), 'six photo tiles');
        $this->assertStringContainsString('gsm-blackletter', $html);

        // Full-bleed strip sits ABOVE the <footer> element.
        $this->assertLessThan(
            strpos($html, '<footer'),
            strpos($html, 'gsm-insta'),
            'Instagram strip must render before the footer element'
        );
    }

    public function test_footer_background_image_and_overlay_render(): void
    {
        $this->seedFooter();

        $bg = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/footer_bg.png', 'filename' => 'footer_bg.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);

        $this->actingAs($this->admin())->put('/cms/footer-builder', [
            'bg_color' => '#101828',
            'text_color' => '#e8e8ef',
            'bg_media_id' => $bg->id,
            'overlay_opacity' => 70,
            'container' => 'container',
        ])->assertRedirect();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-footer-has-bg', $html);
        $this->assertStringContainsString($bg->url, $html);
        $this->assertStringContainsString('gsm-footer-overlay', $html);
        $this->assertStringContainsString('opacity: 0.7', $html);
    }

    public function test_footer_right_side_image_renders_independently_of_background(): void
    {
        $this->seedFooter();

        $bg = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/footer_bg.png', 'filename' => 'footer_bg.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);
        $side = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/footer-image.png', 'filename' => 'footer-image.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100, 'alt' => 'Footer models',
        ]);

        $this->actingAs($this->admin())->put('/cms/footer-builder', [
            'bg_color' => '#101828',
            'text_color' => '#e8e8ef',
            'bg_media_id' => $bg->id,
            'side_media_id' => $side->id,
            'overlay_opacity' => 70,
            'container' => 'container',
        ])->assertRedirect();

        $html = $this->get('/')->assertOk()->getContent();

        // Both layers present, distinct files, side image on its own class.
        $this->assertStringContainsString($bg->url, $html);
        $this->assertStringContainsString($side->url, $html);
        $this->assertStringContainsString('gsm-footer-side', $html);
        $this->assertStringContainsString('Footer models', $html);

        // Replacing only the side image leaves the background untouched.
        $newSide = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/footer-image-2.png', 'filename' => 'footer-image-2.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);
        $this->actingAs($this->admin())->put('/cms/footer-builder', [
            'bg_color' => '#101828',
            'text_color' => '#e8e8ef',
            'bg_media_id' => $bg->id,
            'side_media_id' => $newSide->id,
            'overlay_opacity' => 70,
            'container' => 'container',
        ])->assertRedirect();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString($bg->url, $html);
        $this->assertStringContainsString($newSide->url, $html);
        $this->assertStringNotContainsString($side->url, $html);
    }

    public function test_footer_renders_without_side_image_when_not_set(): void
    {
        $this->seedFooter();

        $html = $this->get('/')->assertOk()->getContent();

        // Fresh seed has no side_media_id — the layer must not render.
        $this->assertStringNotContainsString('gsm-footer-side', $html);
    }

    /* ── Live preview ───────────────────────────────────────────── */

    public function test_builder_page_contains_live_preview_iframe(): void
    {
        $this->actingAs($this->admin())
            ->get('/cms/footer-builder')
            ->assertOk()
            ->assertSee('gsm-live-preview', false)
            ->assertSee(route('front.home'), false);
    }
}
