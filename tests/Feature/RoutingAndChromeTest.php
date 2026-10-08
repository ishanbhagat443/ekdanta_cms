<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cover for the audited routing/chrome fixes: the dead /home redirect, the
 * missing /blog aliases, and the broken sidebar brand image.
 */
class RoutingAndChromeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (config('gsm.roles') as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function staff(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    /* ── Post-login landing (was /home, which had no route) ──────── */

    public function test_authenticated_visitor_on_the_login_page_lands_on_the_dashboard(): void
    {
        $this->actingAs($this->staff())
            ->get('/cms/login')
            ->assertRedirect('/cms/dashboard');
    }

    public function test_the_home_constant_points_at_a_route_that_exists(): void
    {
        $home = \App\Providers\RouteServiceProvider::HOME;

        $this->assertSame('/cms/dashboard', $home);

        $this->actingAs($this->staff())->get($home)->assertOk();
    }

    /* ── /blog → /blogs ─────────────────────────────────────────── */

    public function test_singular_blog_url_redirects_permanently(): void
    {
        $this->get('/blog')->assertRedirect('/blogs')->assertStatus(301);
    }

    public function test_singular_blog_post_url_redirects_permanently(): void
    {
        Post::create([
            'title' => 'Silk Guide', 'slug' => 'silk-guide', 'body' => 'x',
            'status' => Post::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        $this->get('/blog/silk-guide')->assertRedirect('/blogs/silk-guide')->assertStatus(301);
        $this->get('/blogs/silk-guide')->assertOk();
    }

    public function test_the_page_catchall_still_works_for_other_slugs(): void
    {
        Page::create([
            'title' => 'About Us', 'slug' => 'about-us',
            'status' => Page::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        $this->get('/about-us')->assertOk();
        $this->get('/no-such-page')->assertNotFound();
    }

    /* ── Sitemap freshness ──────────────────────────────────────── */

    /**
     * The sitemap is cached for an hour. Publishing or removing content has
     * to drop that cache, or the sitemap advertises pages that are gone and
     * omits ones that exist until the TTL happens to lapse.
     */
    public function test_creating_a_published_page_updates_the_sitemap_immediately(): void
    {
        $this->get('/sitemap.xml')->assertOk(); // prime the cache

        app(\App\Services\PageService::class)->createPage([
            'title' => 'Fresh Page', 'slug' => 'fresh-page', 'status' => Page::STATUS_PUBLISHED,
        ]);

        $this->get('/sitemap.xml')->assertOk()->assertSee('fresh-page');
    }

    public function test_blog_posts_update_the_sitemap_on_create_and_delete(): void
    {
        $author = $this->staff();

        $this->get('/sitemap.xml')->assertOk(); // prime the cache

        $blog = app(\App\Services\BlogService::class);
        $post = $blog->createPost([
            'title' => 'Fresh Post', 'slug' => 'fresh-post', 'body' => '<p>x</p>',
            'status' => Post::STATUS_PUBLISHED, 'published_at' => now(),
        ], $author->id);

        $this->get('/sitemap.xml')->assertOk()->assertSee('fresh-post');

        $blog->deletePost($post);

        $this->get('/sitemap.xml')->assertOk()->assertDontSee('fresh-post');
    }

    /* ── Sidebar brand ──────────────────────────────────────────── */

    public function test_sidebar_brand_renders_no_image_when_no_logo_is_configured(): void
    {
        $html = $this->actingAs($this->staff())->get('/cms/dashboard')->assertOk()->getContent();

        // The bug: asset(null) rendered src="/" — a broken-image glyph.
        $this->assertStringNotContainsString('<img src="/"', $html);
        // The class lands on the <a>, not just in the stylesheet rule.
        $this->assertStringContainsString('class="brand-link gsm-brand-no-logo', $html);
        $this->assertStringContainsString('Ekdanta', $html, 'brand text still shows');
    }

    public function test_sidebar_brand_uses_the_uploaded_branding_logo_when_present(): void
    {
        Setting::create([
            'group' => 'branding',
            'key' => 'logo_light',
            'value' => 'media/2026/08/logo-light.png',
            'type' => 'image',
        ]);

        app(\App\Services\SettingsService::class)->flushCache();

        $html = $this->actingAs($this->staff())->get('/cms/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('logo-light.png', $html);
        $this->assertStringNotContainsString('class="brand-link gsm-brand-no-logo', $html);
    }
}
