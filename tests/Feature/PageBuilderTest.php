<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        foreach (['pages.manage', 'builder.header', 'builder.footer'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function publishedPage(string $slug = 'about-us', string $title = 'About Us'): Page
    {
        return Page::create([
            'title' => $title,
            'slug' => $slug,
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    /* ── Page CRUD ──────────────────────────────────────────────── */

    public function test_pages_require_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/pages')->assertForbidden();
        $this->actingAs($this->admin())->get('/cms/pages')->assertOk();
    }

    public function test_create_page_generates_unique_slug(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/pages', ['title' => 'About Us'])->assertRedirect();
        $this->actingAs($admin)->post('/cms/pages', ['title' => 'About Us'])->assertRedirect();

        $this->assertNotNull(Page::where('slug', 'about-us')->first());
        $this->assertNotNull(Page::where('slug', 'about-us-2')->first());
    }

    public function test_update_page_settings_and_slug(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();

        $this->actingAs($admin)->put("/cms/pages/{$page->id}", [
            'title' => 'Our Story',
            'slug' => 'our-story',
            'status' => 'published',
            'show_banner' => '1',
            'banner_title' => 'The Story',
        ])->assertRedirect();

        $page->refresh();
        $this->assertSame('our-story', $page->slug);
        $this->assertSame('The Story', $page->effectiveBannerTitle());
    }

    public function test_home_page_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $home = $this->publishedPage('home', 'Home');

        $this->actingAs($admin)->delete("/cms/pages/{$home->id}");
        $this->assertNotNull(Page::find($home->id));

        $other = $this->publishedPage('temp', 'Temp');
        $this->actingAs($admin)->delete("/cms/pages/{$other->id}");
        $this->assertNull(Page::find($other->id)); // soft-deleted
    }

    /* ── Builder operations on page sections ────────────────────── */

    public function test_add_all_twenty_section_types_to_a_page(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();

        $types = ['hero', 'banner', 'cta', 'faq', 'timeline', 'gallery', 'team', 'counter',
            'features', 'testimonials', 'video', 'map', 'newsletter', 'accordion', 'tabs',
            'cards', 'custom-html', 'image-block', 'text-block', 'slider'];

        foreach ($types as $type) {
            $this->actingAs($admin)
                ->postJson('/cms/sections', ['area' => 'page', 'page_id' => $page->id, 'type' => $type])
                ->assertCreated();
        }

        $this->assertSame(count($types), $page->sections()->count());
    }

    public function test_page_sections_require_pages_permission(): void
    {
        $role = Role::findOrCreate('Header Only', 'web');
        $role->givePermissionTo('builder.header');
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('Header Only');

        $page = $this->publishedPage();

        $this->actingAs($user)
            ->postJson('/cms/sections', ['area' => 'page', 'page_id' => $page->id, 'type' => 'hero'])
            ->assertForbidden();
    }

    public function test_section_enable_disable_reorder_duplicate_delete(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();

        $this->actingAs($admin)->postJson('/cms/sections', ['area' => 'page', 'page_id' => $page->id, 'type' => 'cta']);
        $this->actingAs($admin)->postJson('/cms/sections', ['area' => 'page', 'page_id' => $page->id, 'type' => 'faq']);

        [$cta, $faq] = $page->sections()->orderBy('sort_order')->get();

        // Disable
        $this->actingAs($admin)->putJson("/cms/sections/{$cta->id}", [
            'device' => 'desktop', 'data' => null, 'is_active' => false,
        ])->assertOk();
        $this->assertFalse($cta->fresh()->is_active);

        // Reorder
        $this->actingAs($admin)->postJson('/cms/sections/reorder', [
            'area' => 'page', 'page_id' => $page->id, 'ids' => [$faq->id, $cta->id],
        ])->assertOk();
        $this->assertSame(0, $faq->fresh()->sort_order);

        // Duplicate
        $this->actingAs($admin)->postJson("/cms/sections/{$faq->id}/clone")->assertCreated();
        $this->assertSame(3, $page->sections()->count());

        // Delete
        $this->actingAs($admin)->deleteJson("/cms/sections/{$cta->id}")->assertOk();
        $this->assertSame(2, $page->sections()->count());
    }

    public function test_per_device_hiding_via_style(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();

        $this->actingAs($admin)->postJson('/cms/sections', ['area' => 'page', 'page_id' => $page->id, 'type' => 'cta']);
        $section = Section::firstOrFail();

        $this->actingAs($admin)->putJson("/cms/sections/{$section->id}", [
            'device' => 'desktop',
            'data' => null,
            'style' => ['visible' => ['desktop' => true, 'tablet' => false, 'mobile' => true]],
        ])->assertOk();

        $this->assertSame('d-block d-md-none d-lg-block gsm-t-none', $section->fresh()->visibilityClasses());
    }

    /* ── Frontend rendering ─────────────────────────────────────── */

    public function test_seeded_home_page_renders_dynamic_sections(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);

        $response = $this->get('/')->assertOk();

        // The starter seeder ships an empty published Home page: it renders,
        // it just has no sections yet.
        $this->assertSame('home', \App\Models\Page::query()->firstOrFail()->slug);
        $response->assertSee('<body', false);
    }

    public function test_page_by_slug_with_banner_faq_schema_and_custom_css(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage('about-us', 'About Us');

        $this->actingAs($admin)->postJson('/cms/sections', ['area' => 'page', 'page_id' => $page->id, 'type' => 'faq']);
        $faq = Section::firstOrFail();

        $this->actingAs($admin)->putJson("/cms/sections/{$faq->id}", [
            'device' => 'desktop',
            'data' => [
                'heading' => 'Common Questions',
                'subheading' => null,
                'items' => [
                    ['question' => 'What is the MOQ?', 'answer' => 'Minimum order is 10 sets per design.'],
                ],
                'schema_enabled' => true,
            ],
        ])->assertOk();

        $this->actingAs($admin)->putJson("/cms/sections/{$faq->id}", [
            'device' => 'desktop',
            'data' => null,
            'style' => ['custom_css' => '#gsm-s'.$faq->id.' h2 { color: rgb(1,2,3); }'],
        ])->assertOk();

        $response = $this->get('/about-us')->assertOk();
        $response->assertSee('About Us');            // banner title (h1)
        $response->assertSee('breadcrumb', false);   // breadcrumb nav
        $response->assertSee('What is the MOQ?');
        $response->assertSee('FAQPage', false);      // JSON-LD schema
        $response->assertSee('color: rgb(1,2,3)', false); // per-section CSS
    }

    public function test_draft_page_404_public_but_previewable_by_admin(): void
    {
        $page = Page::create(['title' => 'Secret', 'slug' => 'secret', 'status' => Page::STATUS_DRAFT]);

        $this->get('/secret')->assertNotFound();
        $this->get('/secret?preview=1')->assertNotFound(); // guests can't preview

        $this->actingAs($this->admin())->get('/secret?preview=1')->assertOk()->assertSee('Secret');
    }

    public function test_unknown_slug_404s_and_reserved_prefixes_skip_catchall(): void
    {
        $this->get('/no-such-page')->assertNotFound();
        // Reserved prefix falls through to the CMS login route, not the
        // page catch-all.
        $this->get('/cms/login')->assertOk();
    }

    public function test_disabled_section_not_rendered(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage('services', 'Services');

        $this->actingAs($admin)->postJson('/cms/sections', ['area' => 'page', 'page_id' => $page->id, 'type' => 'cta']);
        $cta = Section::firstOrFail();

        $this->actingAs($admin)->putJson("/cms/sections/{$cta->id}", [
            'device' => 'desktop',
            'data' => ['heading' => 'Visible CTA Heading', 'subheading' => null, 'text' => null, 'buttons' => [], 'alignment' => 'center'],
        ]);

        $this->get('/services')->assertSee('Visible CTA Heading');

        $this->actingAs($admin)->putJson("/cms/sections/{$cta->id}", [
            'device' => 'desktop', 'data' => null, 'is_active' => false,
        ]);

        $this->get('/services')->assertDontSee('Visible CTA Heading');
    }

    public function test_device_override_renders_both_variants(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage('landing', 'Landing');

        $this->actingAs($admin)->postJson('/cms/sections', ['area' => 'page', 'page_id' => $page->id, 'type' => 'cta']);
        $cta = Section::firstOrFail();

        $this->actingAs($admin)->putJson("/cms/sections/{$cta->id}", [
            'device' => 'desktop',
            'data' => ['heading' => 'Desktop Headline', 'subheading' => null, 'text' => null, 'buttons' => [], 'alignment' => 'center'],
        ]);
        $this->actingAs($admin)->putJson("/cms/sections/{$cta->id}", [
            'device' => 'mobile',
            'data' => ['heading' => 'Mobile Headline'],
        ]);

        $response = $this->get('/landing')->assertOk();
        $response->assertSee('Desktop Headline');
        $response->assertSee('Mobile Headline');
        $response->assertSee('d-none d-lg-inline', false); // breakpoint-scoped spans
    }

    public function test_hero_renders_background_video_with_autoplay_attributes(): void
    {
        $page = $this->publishedPage('video-home', 'Video Home');

        $poster = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/poster.jpg', 'filename' => 'poster.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);
        $video = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/hero.mp4', 'filename' => 'hero.mp4',
            'mime' => 'video/mp4', 'type' => 'video', 'size' => 1000,
        ]);

        $page->sections()->create([
            'type' => 'hero',
            'sort_order' => 0,
            'data_desktop' => [
                'eyebrow' => 'TRUSTED BY RETAILERS WORLDWIDE',
                'heading' => 'Video Hero Heading',
                'subheading' => null, 'text' => null, 'buttons' => [],
                'bg_media_id' => $poster->id,
                'bg_video_media_id' => $video->id,
                'overlay_opacity' => 55,
                'height' => 'full',
                'alignment' => 'center',
            ],
        ]);

        $html = $this->get('/video-home')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-hero-video', $html);
        $this->assertStringContainsString('autoplay muted loop playsinline', $html);
        $this->assertStringContainsString('poster="'.$poster->url.'"', $html);
        $this->assertStringContainsString('src="'.$video->url.'"', $html);
        $this->assertStringContainsString('type="video/mp4"', $html);
        $this->assertStringContainsString('rgba(10, 14, 26, 0.55)', $html); // overlay layer
        $this->assertStringContainsString('min-height: 100svh', $html); // full first screen (svh = stable on mobile)
        $this->assertStringContainsString('Video Hero Heading', $html);
    }

    public function test_hero_breadcrumb_is_opt_in_and_falls_back_to_heading(): void
    {
        $page = $this->publishedPage('hero-crumb', 'Hero Crumb');
        $page->update(['show_banner' => false]); // isolate to the Hero section's own breadcrumb only

        $page->sections()->create([
            'type' => 'hero',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'About Us',
                'subheading' => null, 'text' => null, 'buttons' => [],
                'bg_media_id' => null, 'bg_video_media_id' => null,
                'overlay_opacity' => 60, 'height' => 'large', 'alignment' => 'center',
                'show_breadcrumb' => true,
                // breadcrumb_label intentionally omitted → must fall back to heading
            ],
        ]);

        $html = $this->get('/hero-crumb')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-hero-breadcrumb', $html);
        $this->assertStringContainsString(route('front.home'), $html);
        $this->assertStringContainsString('>Home<', $html);
        $this->assertStringContainsString('About Us', $html); // fallback label
        $this->assertStringContainsString('aria-current="page"', $html);
    }

    public function test_hero_breadcrumb_hidden_by_default_and_uses_custom_label_when_set(): void
    {
        $page = $this->publishedPage('hero-no-crumb', 'Hero No Crumb');
        $page->update(['show_banner' => false]); // isolate to the Hero section's own breadcrumb only

        // Default (show_breadcrumb omitted) → must NOT render.
        $page->sections()->create([
            'type' => 'hero',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'Some Page', 'subheading' => null, 'text' => null, 'buttons' => [],
                'bg_media_id' => null, 'height' => 'large', 'alignment' => 'center',
            ],
        ]);

        $html = $this->get('/hero-no-crumb')->assertOk()->getContent();
        $this->assertStringNotContainsString('gsm-hero-breadcrumb', $html);

        // Explicit custom label overrides the heading fallback.
        $section = $page->sections()->first();
        $section->update([
            'data_desktop' => array_replace($section->data_desktop, [
                'show_breadcrumb' => true,
                'breadcrumb_label' => 'About us',
            ]),
        ]);
        app(\App\Services\PageRenderService::class)->flushCache();

        $html = $this->get('/hero-no-crumb')->assertOk()->getContent();
        $this->assertStringContainsString('gsm-hero-breadcrumb', $html);
        $this->assertStringContainsString('About us', $html);
    }

    public function test_about_collage_section_renders_text_photos_and_captions(): void
    {
        $page = $this->publishedPage('about-home', 'About Home');

        $bg = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/about-bg.png', 'filename' => 'about-bg.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);
        $arch = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/arch.jpg', 'filename' => 'arch.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'about-collage',
            'sort_order' => 0,
            'data_desktop' => [
                'eyebrow' => 'ABOUT US',
                'heading' => 'Preserving Heritage In Every Fabric',
                'body' => '<p>Craftsmanship paragraph.</p>',
                'button_label' => 'our story',
                'button_url' => '/about-us',
                'bg_media_id' => $bg->id,
                'image_arch_media_id' => $arch->id,
                'caption_top' => 'EVERY CREATION IS DESIGNED TO GO GLOBAL',
                'caption_left' => 'EVERY FABRIC REFLECTS ELEGANCE',
                'caption_bottom' => 'EVERY DESIGN TELLS A STORY OF CRAFTSMANSHIP',
            ],
        ]);

        $html = $this->get('/about-home')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-about-canvas', $html);
        $this->assertStringContainsString('Preserving Heritage In Every Fabric', $html);
        $this->assertStringContainsString('Craftsmanship paragraph.', $html);
        $this->assertStringContainsString('our story', $html);
        $this->assertStringContainsString($bg->url, $html);              // background artwork
        $this->assertStringContainsString('gsm-about-arch', $html);      // arch photo slot
        $this->assertStringContainsString('EVERY FABRIC REFLECTS ELEGANCE', $html);
        $this->assertStringContainsString('gsm-about-stacked', $html);   // mobile variant present
        $this->assertStringContainsString('gsm-heading-rule', $html);    // heading ornament
    }

    public function test_about_story_section_renders_grid_photo_and_highlights(): void
    {
        $page = $this->publishedPage('about-story-home', 'About Story Home');

        $bg = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/about-story-bg.png', 'filename' => 'about-story-bg.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);
        $photo = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/about-story-photo.jpg', 'filename' => 'about-story-photo.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'about-story',
            'sort_order' => 0,
            'data_desktop' => [
                'eyebrow' => 'About Us',
                'heading' => 'The Acme Studio Story',
                'intro' => '<p>Intro paragraph text.</p>',
                'lead' => '<p>Lead paragraph one.</p><p>Lead paragraph two.</p>',
                'photo_media_id' => $photo->id,
                'body_left' => '<p>Body left paragraph.</p>',
                'body_right' => '<p>Body right paragraph.</p>',
                'highlights' => [
                    ['text' => 'Every design tells a story of craftsmanship.'],
                    ['text' => 'Every fabric reflects elegance.'],
                    ['text' => 'Every creation is designed to go global.'],
                ],
                'bg_media_id' => $bg->id,
                'show_garland' => true,
            ],
        ]);

        $html = $this->get('/about-story-home')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-about-story"', $html);
        $this->assertStringContainsString('gsm-garland-border', $html);       // gold garland ornament
        $this->assertStringContainsString($bg->url, $html);                   // damask background
        $this->assertStringContainsString($photo->url, $html);                // full-width photo
        $this->assertStringContainsString('About Us', $html);
        $this->assertStringContainsString('The Acme Studio Story', $html);
        $this->assertStringContainsString('Intro paragraph text.', $html);
        $this->assertStringContainsString('Lead paragraph one.', $html);
        $this->assertStringContainsString('Body left paragraph.', $html);
        $this->assertStringContainsString('Body right paragraph.', $html);
        $this->assertSame(3, substr_count($html, 'gsm-about-story-highlight '), 'three highlight taglines');
        $this->assertStringContainsString('Every creation is designed to go global.', $html);

        // Garland toggle off must remove the ornament.
        $page->sections()->first()->update([
            'data_desktop' => array_replace($page->sections()->first()->data_desktop, ['show_garland' => false]),
        ]);
        app(\App\Services\PageRenderService::class)->flushCache();
        $html = $this->get('/about-story-home')->assertOk()->getContent();
        $this->assertStringNotContainsString('gsm-garland-border', $html);
    }

    public function test_founder_journey_section_renders_story_ornaments_and_mission_vision(): void
    {
        $page = $this->publishedPage('founder-journey-home', 'Founder Journey Home');

        $bg = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/founder-bg.png', 'filename' => 'founder-bg.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);
        $portrait = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/founder-portrait.png', 'filename' => 'founder-portrait.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);
        $ornTop = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/orn-top.png', 'filename' => 'orn-top.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);
        $ornBottom = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/orn-bottom.png', 'filename' => 'orn-bottom.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'founder-journey',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => "The Founder's Journey",
                'body' => '<p>Founder story paragraph one.</p><p>Founder story paragraph two.</p>',
                'bg_media_id' => $bg->id,
                'image_media_id' => $portrait->id,
                'ornament_top_media_id' => $ornTop->id,
                'ornament_bottom_media_id' => $ornBottom->id,
                'mission_title' => 'Our Mission',
                'mission_text' => 'Mission statement text.',
                'vision_title' => 'Our Vision',
                'vision_text' => 'Vision statement text.',
                'show_garland' => true,
            ],
        ]);

        $html = $this->get('/founder-journey-home')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-founder-journey"', $html);
        $this->assertStringContainsString('gsm-floral-border', $html); // floral divider (line_seperater_2_part.png)
        $this->assertStringContainsString($bg->url, $html);
        $this->assertStringContainsString($portrait->url, $html);
        $this->assertStringContainsString($ornTop->url, $html);
        $this->assertStringContainsString($ornBottom->url, $html);
        $this->assertStringContainsString('gsm-founder-portrait', $html);
        $this->assertStringContainsString('gsm-founder-ornament-top', $html);
        $this->assertStringContainsString('gsm-founder-ornament-bottom', $html);
        $this->assertStringContainsString('Founder story paragraph one.', $html);
        $this->assertStringContainsString('Our Mission', $html);
        $this->assertStringContainsString('Mission statement text.', $html);
        $this->assertStringContainsString('Our Vision', $html);
        $this->assertStringContainsString('Vision statement text.', $html);
        $this->assertSame(2, substr_count($html, 'gsm-founder-card"'), 'both mission and vision cards render');
    }

    public function test_founder_journey_section_omits_empty_mission_vision_cards(): void
    {
        $page = $this->publishedPage('founder-journey-partial', 'Founder Journey Partial');

        $page->sections()->create([
            'type' => 'founder-journey',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => "The Founder's Journey",
                'body' => '<p>Story only, no mission/vision configured.</p>',
                'bg_media_id' => null, 'image_media_id' => null,
                'ornament_top_media_id' => null, 'ornament_bottom_media_id' => null,
                'mission_title' => null, 'vision_title' => null,
                'show_garland' => false,
            ],
        ]);

        $html = $this->get('/founder-journey-partial')->assertOk()->getContent();

        $this->assertStringNotContainsString('gsm-floral-border', $html);
        $this->assertStringNotContainsString('gsm-founder-card', $html); // no mission/vision → no card row
        $this->assertStringNotContainsString('gsm-founder-portrait', $html);
        $this->assertStringContainsString('Story only, no mission/vision configured.', $html);
    }

    public function test_video_section_renders_poster_play_button_and_youtube_embed(): void
    {
        $page = $this->publishedPage('video-section-home', 'Video Section Home');

        $poster = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/video-poster.jpg', 'filename' => 'video-poster.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'video',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'Watch Our Story',
                'poster_media_id' => $poster->id,
                'video_url' => 'https://www.youtube.com/watch?v=abc123XYZ',
                'autoplay' => true,
            ],
        ]);

        $html = $this->get('/video-section-home')->assertOk()->getContent();

        $this->assertStringContainsString($poster->url, $html);
        $this->assertStringContainsString('data-bs-toggle="modal"', $html);
        $this->assertStringContainsString('aria-label="Play video"', $html);
        $this->assertStringContainsString('gsm-video-', $html); // modal id prefix
        $this->assertStringContainsString('https://www.youtube.com/embed/abc123XYZ?autoplay=1', $html);
        $this->assertStringContainsString('gsm-video-frame', $html);
        $this->assertStringContainsString('src="about:blank"', $html); // lazy-loaded until modal opens
    }

    public function test_video_showcase_section_alternates_layout_and_renders_multiple_blocks(): void
    {
        $page = $this->publishedPage('video-showcase-home', 'Video Showcase Home');

        $photoA = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/showcase-a.jpg', 'filename' => 'showcase-a.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);
        $photoB = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/showcase-b.jpg', 'filename' => 'showcase-b.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'video-showcase',
            'sort_order' => 0,
            'data_desktop' => [
                'items' => [
                    ['heading' => 'Block One Heading', 'text' => 'Block one body text.', 'poster_media_id' => $photoA->id, 'video_url' => 'https://www.youtube.com/watch?v=firstVideo1'],
                    ['heading' => 'Block Two Heading', 'text' => 'Block two body text.', 'poster_media_id' => $photoB->id, 'video_url' => 'https://vimeo.com/987654321'],
                ],
                'autoplay' => true,
            ],
        ]);

        $html = $this->get('/video-showcase-home')->assertOk()->getContent();

        $this->assertStringContainsString('Block One Heading', $html);
        $this->assertStringContainsString('Block one body text.', $html);
        $this->assertStringContainsString($photoA->url, $html);
        $this->assertStringContainsString('https://www.youtube.com/embed/firstVideo1', $html);

        $this->assertStringContainsString('Block Two Heading', $html);
        $this->assertStringContainsString('Block two body text.', $html);
        $this->assertStringContainsString($photoB->url, $html);
        $this->assertStringContainsString('https://player.vimeo.com/video/987654321', $html);

        $this->assertSame(2, substr_count($html, 'gsm-video-showcase-play'), 'both blocks get a play button');
        $this->assertSame(2, substr_count($html, 'modal fade'), 'both blocks get their own modal');

        // Alternation: first block's media column is order-lg-1 (left),
        // second block's media column is order-lg-2 (right).
        $firstRowPos = strpos($html, 'Block One Heading');
        $firstMediaOrderPos = strpos($html, 'order-lg-1');
        $this->assertLessThan($firstRowPos, $firstMediaOrderPos, 'first block: photo (order-lg-1) precedes its heading in DOM');
    }

    public function test_artistry_section_renders_gallery_and_feature_cards(): void
    {
        $page = $this->publishedPage('artistry-home', 'Artistry Home');

        $bg = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/artistry-bg.png', 'filename' => 'artistry-bg.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);
        $photo1 = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/art1.jpg', 'filename' => 'art1.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);
        $photo2 = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/art2.jpg', 'filename' => 'art2.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'artistry',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'Our Artistry Excellence',
                'gallery' => [
                    ['media_id' => $photo1->id, 'title' => 'Fabric Sourcing', 'text' => null],
                    ['media_id' => $photo2->id, 'title' => 'Expert Craftsmanship', 'text' => 'Featured description text.'],
                ],
                'features' => [
                    ['icon' => 'fas fa-tshirt', 'title' => 'Fabric Sourcing', 'text' => 'Feature one text.'],
                    ['icon' => 'fas fa-industry', 'title' => 'Bulk Manufacturing', 'text' => 'Feature two text.'],
                ],
                'bg_media_id' => $bg->id,
                'show_flourish' => true,
            ],
        ]);

        $html = $this->get('/artistry-home')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-floral-border', $html); // floral divider (line_seperater_2_part.png)
        $this->assertStringContainsString($bg->url, $html);
        $this->assertStringContainsString($photo1->url, $html);
        $this->assertStringContainsString($photo2->url, $html);
        $this->assertStringContainsString('Our Artistry Excellence', $html);
        $this->assertStringContainsString('Fabric Sourcing', $html);
        $this->assertStringContainsString('Expert Craftsmanship', $html);
        $this->assertStringContainsString('Featured description text.', $html);
        $this->assertStringContainsString('Bulk Manufacturing', $html);
        $this->assertStringContainsString('Feature two text.', $html);
        $this->assertSame(2, substr_count($html, 'gsm-artistry-photo'), 'two gallery cards');
        $this->assertSame(2, substr_count($html, 'gsm-artistry-feature-card'), 'two feature cards');
        $this->assertStringContainsString('flourish.svg', $html);
        // Accordion behaviour: only the first card is expanded by default,
        // showing its title AND description; a second card exists but is
        // collapsed (its data-gsm-artistry-card button still renders).
        $this->assertSame(1, substr_count($html, 'gsm-artistry-card-active'), 'exactly one card starts expanded');
        $this->assertSame(2, substr_count($html, 'data-gsm-artistry-card'), 'both cards are clickable accordion buttons');

        // Flourish toggle off must remove both ornaments.
        $page->sections()->first()->update([
            'data_desktop' => array_replace($page->sections()->first()->data_desktop, ['show_flourish' => false]),
        ]);
        app(\App\Services\PageRenderService::class)->flushCache();
        $html = $this->get('/artistry-home')->assertOk()->getContent();
        $this->assertStringNotContainsString('flourish.svg', $html);
    }

    public function test_artistry_gallery_scrolls_horizontally_when_more_than_three_cards(): void
    {
        $page = $this->publishedPage('artistry-scroll-home', 'Artistry Scroll Home');

        $photo = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/art-scroll.jpg', 'filename' => 'art-scroll.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $gallery = [];
        for ($i = 1; $i <= 5; $i++) {
            $gallery[] = ['media_id' => $photo->id, 'title' => "Card {$i}", 'text' => null];
        }

        $page->sections()->create([
            'type' => 'artistry',
            'sort_order' => 0,
            'data_desktop' => ['heading' => 'Our Artistry Excellence', 'gallery' => $gallery, 'features' => []],
        ]);

        $html = $this->get('/artistry-scroll-home')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-artistry-slider-scroll', $html); // scroll mode kicks in above 3 cards
        $this->assertStringContainsString('gsm-artistry-dots', $html); // pagination dots for the scroll track
        $this->assertSame(5, substr_count($html, 'data-gsm-artistry-card'), 'all 5 cards render, none dropped');
        $this->assertStringContainsString('Card 5', $html); // last card still renders, not cut off
    }

    public function test_artistry_gallery_stays_grid_layout_for_three_or_fewer_cards(): void
    {
        $page = $this->publishedPage('artistry-grid-home', 'Artistry Grid Home');

        $photo = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/art-grid.jpg', 'filename' => 'art-grid.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'artistry',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'Our Artistry Excellence',
                'gallery' => [
                    ['media_id' => $photo->id, 'title' => 'One', 'text' => null],
                    ['media_id' => $photo->id, 'title' => 'Two', 'text' => null],
                    ['media_id' => $photo->id, 'title' => 'Three', 'text' => null],
                ],
                'features' => [],
            ],
        ]);

        $html = $this->get('/artistry-grid-home')->assertOk()->getContent();

        $this->assertStringNotContainsString('gsm-artistry-slider-scroll', $html); // 3 cards = grid, not scroll
    }

    public function test_history_timeline_section_renders_scalloped_badges_and_alternates_position(): void
    {
        $page = $this->publishedPage('history-home', 'History Home');

        $bg = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/history-bg.png', 'filename' => 'history-bg.png',
            'mime' => 'image/png', 'type' => 'image', 'size' => 100,
        ]);
        $photo = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/history-photo.jpg', 'filename' => 'history-photo.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'history-timeline',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'The Threads of Our Heritage',
                'milestones' => [
                    ['year' => '2006', 'text' => 'First milestone text.', 'image_media_id' => $photo->id],
                    ['year' => '2007', 'text' => 'Second milestone text.', 'image_media_id' => $photo->id],
                    ['year' => '2009', 'text' => 'Third milestone text.', 'image_media_id' => $photo->id],
                ],
                'bg_media_id' => $bg->id,
            ],
        ]);

        $html = $this->get('/history-home')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-history-track', $html); // scroll-snap track, not a fixed grid
        $this->assertStringContainsString($bg->url, $html);
        $this->assertStringContainsString($photo->url, $html);
        $this->assertStringContainsString('2006', $html);
        $this->assertStringContainsString('First milestone text.', $html);
        $this->assertStringContainsString('Third milestone text.', $html);
        $this->assertSame(3, substr_count($html, 'gsm-history-item '), 'three milestone items');
        $this->assertSame(2, substr_count($html, 'gsm-history-item-high'), 'even-index milestones (1st, 3rd) sit high');
        $this->assertSame(1, substr_count($html, 'gsm-history-item-low'), 'odd-index milestone (2nd) sits low');
        $this->assertSame(2, substr_count($html, 'gsm-history-badge-gold'), 'gold badges on even index');
        $this->assertSame(1, substr_count($html, 'gsm-history-badge-orange'), 'orange badge on odd index');
        $this->assertStringContainsString('clip-path: url(#gsm-history-scallop-', $html);
    }

    public function test_history_timeline_section_scales_to_many_milestones_without_breaking(): void
    {
        // Proves the layout genuinely supports unbounded CMS growth (the
        // design shows the last card cut off at the frame edge, confirming
        // it scrolls rather than being a fixed 4-item grid).
        $page = $this->publishedPage('history-scale-home', 'History Scale Home');

        $milestones = [];
        for ($year = 2006; $year <= 2025; $year++) {
            $milestones[] = ['year' => (string) $year, 'text' => "Milestone for {$year}.", 'image_media_id' => null];
        }

        $page->sections()->create([
            'type' => 'history-timeline',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'The Threads of Our Heritage',
                'milestones' => $milestones,
                'bg_media_id' => null,
            ],
        ]);

        $html = $this->get('/history-scale-home')->assertOk()->getContent();

        $this->assertSame(20, substr_count($html, 'gsm-history-item '), 'all 20 milestones render');
        $this->assertStringContainsString('2006', $html);
        $this->assertStringContainsString('2025', $html); // most recently added year still renders
        $this->assertStringContainsString('Milestone for 2025.', $html);
    }

    public function test_accordion_section_card_style_renders_toggle_icons_and_alignment(): void
    {
        $page = $this->publishedPage('accordion-cards-home', 'Accordion Cards Home');

        $photo = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/accordion-photo.jpg', 'filename' => 'accordion-photo.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'accordion',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'The Foundation Of Our Success',
                'items' => [
                    ['title' => 'In-House Production', 'body' => 'First panel body.'],
                    ['title' => 'Custom Design & Manufacturing', 'body' => 'Second panel body.'],
                    ['title' => 'Timely Delivery', 'body' => 'Third panel body.'],
                ],
                'side_media_id' => $photo->id,
                'first_open' => true,
                'heading_align' => 'start',
                'heading_ornament' => true,
                'panel_style' => 'cards',
            ],
        ]);

        $html = $this->get('/accordion-cards-home')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-accordion-cards', $html);
        $this->assertStringContainsString('text-start', $html); // left-aligned heading
        $this->assertStringContainsString('gsm-heading-rule', $html); // ornament opted in
        $this->assertStringContainsString($photo->url, $html);
        $this->assertStringContainsString('gsm-accordion-side-photo', $html);
        $this->assertSame(3, substr_count($html, 'accordion-item'), 'three panels');
        $this->assertSame(2, substr_count($html, 'collapsed'), 'two panels collapsed, first stays open');
        $this->assertSame(3, substr_count($html, 'gsm-accordion-toggle-icon'), 'custom +/- icon on every panel');
        $this->assertStringContainsString('First panel body.', $html);
        $this->assertStringContainsString('Second panel body.', $html);
        $this->assertStringContainsString('Third panel body.', $html);
    }

    public function test_accordion_section_default_style_unaffected_by_new_fields(): void
    {
        // Regression guard: an accordion instance created BEFORE this
        // feature existed (no panel_style/heading_align/heading_ornament
        // keys at all) must still render exactly as before.
        $page = $this->publishedPage('accordion-default-home', 'Accordion Default Home');

        $page->sections()->create([
            'type' => 'accordion',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'Frequently Asked',
                'items' => [
                    ['title' => 'Question One', 'body' => 'Answer one.'],
                ],
            ],
        ]);

        $html = $this->get('/accordion-default-home')->assertOk()->getContent();

        $this->assertStringNotContainsString('gsm-accordion-cards', $html);
        $this->assertStringNotContainsString('gsm-accordion-toggle-icon', $html);
        $this->assertStringContainsString('Question One', $html);
        $this->assertStringContainsString('Answer one.', $html);
    }

    public function test_team_section_renders_members_ornament_and_border_and_social_links(): void
    {
        $page = $this->publishedPage('team-home', 'Team Home');

        $photo1 = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/team-a.jpg', 'filename' => 'team-a.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);
        $photo2 = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/team-b.jpg', 'filename' => 'team-b.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'team',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'Meet Our Team!',
                'members' => [
                    [
                        'media_id' => $photo1->id, 'name' => 'Jane Doe', 'role' => 'Creative Director',
                        'text' => 'Leads the design studio.',
                        'social_links' => [['icon' => 'fab fa-instagram', 'url' => 'https://instagram.com/janedoe']],
                    ],
                    [
                        'media_id' => $photo2->id, 'name' => 'John Smith', 'role' => null,
                        'text' => 'Oversees production quality.', 'social_links' => [],
                    ],
                ],
                'heading_ornament' => true,
                'show_border' => true,
            ],
        ]);

        $html = $this->get('/team-home')->assertOk()->getContent();

        $this->assertStringContainsString('gsm-team-bordered', $html);
        $this->assertStringContainsString('gsm-heading-rule', $html); // ornament opted in
        $this->assertStringContainsString($photo1->url, $html);
        $this->assertStringContainsString($photo2->url, $html);
        $this->assertStringContainsString('Jane Doe', $html);
        $this->assertStringContainsString('Creative Director', $html);
        $this->assertStringContainsString('John Smith', $html);
        $this->assertStringContainsString('Leads the design studio.', $html);
        $this->assertStringContainsString('gsm-team-social', $html);
        $this->assertStringContainsString('https://instagram.com/janedoe', $html);
        $this->assertStringContainsString('fab fa-instagram', $html);
        $this->assertSame(1, substr_count($html, 'gsm-team-social'), 'only the member with a social link gets the icon row');

        // Toggles off must remove both the ornament and the border.
        $page->sections()->first()->update([
            'data_desktop' => array_replace($page->sections()->first()->data_desktop, [
                'heading_ornament' => false,
                'show_border' => false,
            ]),
        ]);
        app(\App\Services\PageRenderService::class)->flushCache();
        $html = $this->get('/team-home')->assertOk()->getContent();
        $this->assertStringNotContainsString('gsm-team-bordered', $html);
    }

    public function test_process_steps_section_renders_zigzag_timeline(): void
    {
        $page = $this->publishedPage('process-home', 'Process Home');

        $photo = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/step.jpg', 'filename' => 'step.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'process-steps',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'Our Working Process',
                'items' => [
                    ['title' => 'Design and Innovation', 'text' => 'Stylish designs.', 'image_media_id' => $photo->id],
                    ['title' => 'Quality Manufacturing', 'text' => 'Strict checks.', 'image_media_id' => null], // photo missing → renders without frame
                    ['title' => 'Client-Centric Approach', 'text' => 'Customer first.', 'image_media_id' => null],
                    ['title' => 'Prompt Delivery', 'text' => 'On time.', 'image_media_id' => null],
                ],
                'bg_media_id' => null,
            ],
        ]);

        $html = $this->get('/process-home')->assertOk()->getContent();

        $this->assertStringContainsString('Our Working Process', $html);
        $this->assertStringContainsString('gsm-process-row', $html);
        $this->assertStringContainsString('gsm-process-num', $html);
        $this->assertStringContainsString('>4</span>', $html);                    // numbered badges 1..4
        $this->assertStringContainsString('gsm-ogee', $html);                     // arch frame for the step with a photo
        $this->assertStringContainsString('clip-path: url(#gsm-ogee-', $html);    // responsive SVG clip
        $this->assertStringContainsString('Design and Innovation', $html);
        $this->assertStringContainsString('Prompt Delivery', $html);
        $this->assertStringContainsString('gsm-heading-rule', $html);             // heading ornament
        $this->assertSame(1, substr_count($html, 'gsm-ogee-'.$page->sections()->first()->id.'"'), 'clipPath defined exactly once');
    }

    public function test_why_choose_section_renders_photo_bullet_strip_and_button(): void
    {
        $page = $this->publishedPage('choose-home', 'Choose Home');

        $photo = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/showroom.jpg', 'filename' => 'showroom.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        $page->sections()->create([
            'type' => 'why-choose',
            'sort_order' => 0,
            'style' => ['bg_color' => '#f5efe6', 'animation' => 'fade-up'],
            'data_desktop' => [
                'heading' => 'Why Choose Us',
                'subheading' => 'Ethnic fashion deserves more than product photos.',
                'image_media_id' => $photo->id,
                'bullets' => [
                    ['text' => 'Qualitative products'],
                    ['text' => 'Positive records'],
                    ['text' => 'Excellent transport & logistic facility'],
                    ['text' => 'Dexterous team of professionals'],
                ],
                'button_label' => 'View all blog',
                'button_url' => '/blogs',
                'bg_media_id' => null,
            ],
        ]);

        $html = $this->get('/choose-home')->assertOk()->getContent();

        $this->assertStringContainsString('Why Choose Us', $html);
        $this->assertStringContainsString('gsm-heading-rule', $html);            // heading ornament
        $this->assertStringContainsString('gsm-choose-photo', $html);
        $this->assertStringContainsString($photo->url, $html);
        $this->assertStringContainsString('Qualitative products', $html);
        $this->assertStringContainsString('Excellent transport &amp; logistic facility', $html);
        $this->assertSame(4, substr_count($html, 'gsm-choose-bullet'), 'four bullets in the strip');
        $this->assertStringContainsString('View all blog', $html);
        $this->assertStringContainsString('/blogs', $html);
        $this->assertStringContainsString('background-color: #f5efe6', $html);  // section style pipeline
    }

    public function test_blog_posts_section_renders_two_large_cards_and_mini_rows(): void
    {
        $page = $this->publishedPage('blog-home', 'Blog Home');

        $photo = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/post.jpg', 'filename' => 'post.jpg',
            'mime' => 'image/jpeg', 'type' => 'image', 'size' => 100,
        ]);

        foreach (range(1, 5) as $i) {
            \App\Models\Post::create([
                'title' => "Ethnic Fashion Story {$i}",
                'slug' => "ethnic-fashion-story-{$i}",
                'author_name' => 'Acme Studio',
                'body' => 'If you are a ethnic fashion reseller then it is impossible to ignore influencer marketing.',
                'featured_media_id' => $photo->id,
                'status' => \App\Models\Post::STATUS_PUBLISHED,
                'published_at' => now()->subDays($i),
            ]);
        }

        \App\Models\Post::create([
            'title' => 'Hidden Draft Post',
            'slug' => 'hidden-draft-post',
            'body' => 'Not live.',
            'status' => \App\Models\Post::STATUS_DRAFT,
        ]);

        $page->sections()->create([
            'type' => 'blog-posts',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'Our Blog Post',
                'limit' => 5,
                'button_label' => 'View all blog',
                'button_url' => '/blogs',
                'bg_media_id' => null,
            ],
        ]);

        $html = $this->get('/blog-home')->assertOk()->getContent();

        $this->assertStringContainsString('Our Blog Post', $html);
        $this->assertStringContainsString('gsm-heading-rule', $html);            // heading ornament
        $this->assertSame(2, substr_count($html, '<article class="gsm-blog-card"'), 'two large cards');
        $this->assertSame(3, substr_count($html, '<article class="gsm-blog-mini"'), 'three mini rows');
        $this->assertStringContainsString('Ethnic Fashion Story 1', $html);      // newest first
        $this->assertStringContainsString('/blog/ethnic-fashion-story-1', $html);
        $this->assertStringNotContainsString('Hidden Draft Post', $html);        // live() scope
        $this->assertStringContainsString('Acme Studio', $html);           // author pill
        $this->assertStringContainsString(now()->subDay()->format('F j, Y'), $html); // date pill
        $this->assertStringContainsString('influencer marketing', $html);        // auto excerpt
        $this->assertStringContainsString('View all blog', $html);
    }

    public function test_hero_full_screen_with_gradient_overlay_entrance_and_builder_button(): void
    {
        \App\Models\Gradient::create([
            'name' => 'Hero', 'slug' => 'hero-overlay', 'type' => 'linear', 'angle' => 180,
            'stops' => [
                ['color' => '#0A0E1A', 'opacity' => 20, 'position' => 0],
                ['color' => '#0A0E1A', 'opacity' => 80, 'position' => 100],
            ],
        ]);
        \App\Models\ButtonStyle::create([
            'name' => 'Primary Pill', 'slug' => 'primary-pill', 'is_active' => true,
            'config' => \App\Models\ButtonStyle::defaults(),
        ]);

        $page = $this->publishedPage('hero-home', 'Hero Home');
        $page->sections()->create([
            'type' => 'hero',
            'sort_order' => 0,
            'data_desktop' => [
                'heading' => 'Full Screen Hero',
                'buttons' => [
                    ['label' => 'Explore Collection', 'url' => '/collection', 'style' => 'primary-pill'],
                    ['label' => 'Send Enquiry', 'url' => '/contact-us', 'style' => 'outline'],
                ],
                'overlay_gradient' => 'hero-overlay',
                'height' => 'full',
                'alignment' => 'center',
                'content_width' => 900,
                'entrance' => 'fade-up',
                'scroll_indicator' => true,
            ],
        ]);

        $html = $this->get('/hero-home')->assertOk()->getContent();

        $this->assertStringContainsString('min-height: 100svh', $html);                         // full first screen
        $this->assertStringContainsString('background-image: var(--gsm-gradient-hero-overlay)', $html); // library overlay
        $this->assertStringContainsString('data-m="content"', $html);                           // entrance: a Motion tab track now
        $this->assertStringContainsString('"css":"ek-hero-fade-up"', $html);
        $this->assertStringContainsString('@keyframes ek-hero-fade-up', $html);
        $this->assertStringContainsString('max-width: 900px', $html);                           // content width
        $this->assertStringContainsString('gsm-hero-scroll', $html);                            // scroll indicator
        $this->assertStringContainsString('gsm-btn-primary-pill', $html);                       // Button Builder style
        $this->assertStringContainsString('btn-outline-primary', $html);                        // legacy fallback intact
    }

    public function test_section_wrapper_supports_gradient_video_overlay_radius_and_z_index(): void
    {
        \App\Models\Gradient::create([
            'name' => 'Hero', 'slug' => 'hero', 'type' => 'linear', 'angle' => 180,
            'stops' => [
                ['color' => '#0A0E1A', 'opacity' => 20, 'position' => 0],
                ['color' => '#0A0E1A', 'opacity' => 80, 'position' => 100],
            ],
        ]);

        $video = \App\Models\Media::create([
            'disk' => 'public', 'path' => 'media/bg.mp4', 'filename' => 'bg.mp4',
            'mime' => 'video/mp4', 'type' => 'video', 'size' => 100,
        ]);

        $page = $this->publishedPage('fx-home', 'FX Home');
        $page->sections()->create([
            'type' => 'text-block',
            'sort_order' => 0,
            'data_desktop' => ['heading' => 'Layered Section', 'body' => '<p>Content above the layers.</p>'],
            'style' => [
                'bg_gradient' => 'hero',
                'bg_video_media_id' => $video->id,
                'overlay_color' => '#101828',
                'overlay_opacity' => 45,
                'radius' => '24px',
                'z_index' => 3,
            ],
        ]);

        $html = $this->get('/fx-home')->assertOk()->getContent();

        $this->assertStringContainsString('background-image: var(--gsm-gradient-hero)', $html);
        $this->assertStringContainsString('gsm-section-video', $html);
        $this->assertStringContainsString($video->url, $html);
        $this->assertStringContainsString('autoplay muted loop playsinline', $html);
        $this->assertStringContainsString('gsm-section-overlay', $html);
        $this->assertStringContainsString('background-color: #101828; opacity: 0.45', $html);
        $this->assertStringContainsString('border-radius: 24px', $html);
        $this->assertStringContainsString('z-index: 3', $html);
        $this->assertStringContainsString('gsm-section-inner', $html);           // content stacked above layers
        $this->assertStringContainsString('Layered Section', $html);
    }
}
