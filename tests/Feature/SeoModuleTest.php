<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeoModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        Permission::findOrCreate('seo.manage', 'web');
        Permission::findOrCreate('pages.manage', 'web');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function publishedPage(array $overrides = []): Page
    {
        return Page::create(array_merge([
            'title' => 'About Us',
            'slug' => 'about-us',
            'status' => Page::STATUS_PUBLISHED,
            'show_banner' => true,
            'published_at' => now(),
        ], $overrides));
    }

    private function setSetting(string $key, string $value): void
    {
        [$group, $name] = explode('.', $key, 2);
        Setting::updateOrCreate(['group' => $group, 'key' => $name], ['value' => $value]);
        app(SettingsService::class)->flushCache();
    }

    /* ── Per-page meta ─────────────────────────────────────────── */

    public function test_page_seo_meta_saved_and_rendered_in_head(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();

        $this->actingAs($admin)->put("/cms/pages/{$page->id}", [
            'title' => 'About Us',
            'status' => 'published',
            'seo_meta_title' => 'About Acme Studio | Surat Manufacturer',
            'seo_meta_description' => 'B2B ethnic fashion manufacturer from Surat.',
            'seo_meta_keywords' => 'silk, sarees, surat',
            'seo_canonical_url' => 'https://globalsilkmills.com/about-us',
            'seo_robots' => 'noindex',
            'seo_og_title' => 'About GSM',
            'seo_og_description' => 'OG description here.',
            'seo_twitter_card' => 'summary_large_image',
            'seo_twitter_title' => 'GSM on Twitter',
            'seo_twitter_description' => 'Twitter description here.',
            'seo_breadcrumb_title' => 'Our Story',
            'seo_schema_json' => '{"@context":"https://schema.org","@type":"AboutPage","name":"About GSM"}',
            'seo_sitemap_include' => 1,
        ])->assertRedirect();

        $page->refresh();
        $this->assertNotNull($page->seoMeta);

        $html = $this->get('/about-us')->assertOk()->getContent();

        $this->assertStringContainsString('<title>About Acme Studio | Surat Manufacturer</title>', $html);
        $this->assertStringContainsString('name="description" content="B2B ethnic fashion manufacturer from Surat."', $html);
        $this->assertStringContainsString('name="keywords" content="silk, sarees, surat"', $html);
        $this->assertStringContainsString('rel="canonical" href="https://globalsilkmills.com/about-us"', $html);
        $this->assertStringContainsString('name="robots" content="noindex"', $html);
        $this->assertStringContainsString('property="og:title" content="About GSM"', $html);
        $this->assertStringContainsString('property="og:description" content="OG description here."', $html);
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $html);
        $this->assertStringContainsString('name="twitter:title" content="GSM on Twitter"', $html);
        $this->assertStringContainsString('name="twitter:description" content="Twitter description here."', $html);
        $this->assertStringContainsString('"@type":"AboutPage"', $html);
        // Breadcrumb JSON-LD with the overridden title
        $this->assertStringContainsString('BreadcrumbList', $html);
        $this->assertStringContainsString('Our Story', $html);
    }

    public function test_settings_defaults_used_when_page_has_no_meta(): void
    {
        $this->setSetting('seo.meta_title', '{page} | Acme Studio');
        $this->setSetting('seo.meta_description', 'Default description.');
        $this->setSetting('seo.meta_keywords', 'default, keywords');
        $this->setSetting('seo.canonical', 'https://gsm.test');

        $this->publishedPage(['title' => 'Our Services', 'slug' => 'our-services']);

        $html = $this->get('/our-services')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Our Services | Acme Studio</title>', $html);
        $this->assertStringContainsString('name="description" content="Default description."', $html);
        $this->assertStringContainsString('name="keywords" content="default, keywords"', $html);
        $this->assertStringContainsString('rel="canonical" href="https://gsm.test/our-services"', $html);
    }

    public function test_twitter_site_handle_rendered_from_settings(): void
    {
        $this->setSetting('seo.twitter_handle', '@globalsilkmills');
        $this->publishedPage();

        $this->get('/about-us')->assertOk()
            ->assertSee('name="twitter:site" content="@globalsilkmills"', false);
    }

    public function test_empty_seo_form_prunes_meta_row(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();
        $page->seoMeta()->create(['meta_title' => 'Old']);

        $this->actingAs($admin)->put("/cms/pages/{$page->id}", [
            'title' => 'About Us',
            'status' => 'published',
            'seo_sitemap_include' => 1,
        ])->assertRedirect();

        $this->assertNull($page->fresh()->seoMeta);
    }

    /* ── XML sitemap ───────────────────────────────────────────── */

    public function test_sitemap_lists_pages_and_posts_with_controls(): void
    {
        $this->setSetting('seo.canonical', 'https://gsm.test');

        $this->publishedPage(['title' => 'Home', 'slug' => 'home']);
        $included = $this->publishedPage(['title' => 'Visible', 'slug' => 'visible-page']);
        $included->seoMeta()->create(['sitemap_priority' => '0.9', 'sitemap_changefreq' => 'daily']);

        $noindexed = $this->publishedPage(['title' => 'Hidden A', 'slug' => 'hidden-noindex']);
        $noindexed->seoMeta()->create(['robots' => 'noindex,nofollow']);

        $excluded = $this->publishedPage(['title' => 'Hidden B', 'slug' => 'hidden-excluded']);
        $excluded->seoMeta()->create(['sitemap_include' => false]);

        $this->publishedPage(['title' => 'Draft', 'slug' => 'draft-page', 'status' => Page::STATUS_DRAFT]);

        Post::create([
            'title' => 'Silk Guide',
            'slug' => 'silk-guide',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'body' => 'Guide body.',
        ]);

        $xml = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->getContent();

        $this->assertStringContainsString('<loc>https://gsm.test</loc>', $xml); // home
        $this->assertStringContainsString('<loc>https://gsm.test/visible-page</loc>', $xml);
        $this->assertStringContainsString('<priority>0.9</priority>', $xml);
        $this->assertStringContainsString('<changefreq>daily</changefreq>', $xml);
        $this->assertStringContainsString('<loc>https://gsm.test/blogs</loc>', $xml);
        $this->assertStringContainsString('<loc>https://gsm.test/blogs/silk-guide</loc>', $xml);
        $this->assertStringNotContainsString('hidden-noindex', $xml);
        $this->assertStringNotContainsString('hidden-excluded', $xml);
        $this->assertStringNotContainsString('draft-page', $xml);
    }

    /* ── robots.txt ────────────────────────────────────────────── */

    public function test_robots_txt_serves_settings_content_plus_sitemap(): void
    {
        $this->setSetting('seo.canonical', 'https://gsm.test');
        $this->setSetting('seo.robots', "User-agent: *\nDisallow: /secret");

        $body = $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->getContent();

        $this->assertStringContainsString('Disallow: /secret', $body);
        $this->assertStringContainsString('Sitemap: https://gsm.test/sitemap.xml', $body);
    }

    public function test_robots_txt_default_when_setting_empty(): void
    {
        $body = $this->get('/robots.txt')->assertOk()->getContent();

        $this->assertStringContainsString('Disallow: /cms', $body);
        $this->assertStringContainsString('Sitemap:', $body);
    }

    /* ── 301 redirects ─────────────────────────────────────────── */

    public function test_active_redirect_301_and_hit_counter(): void
    {
        Redirect::create(['from_path' => '/old-page', 'to_url' => '/new-page', 'status_code' => 301]);

        $this->get('/old-page')
            ->assertStatus(301)
            ->assertRedirect('/new-page');

        $redirect = Redirect::firstOrFail();
        $this->assertSame(1, $redirect->hits);
        $this->assertNotNull($redirect->last_hit_at);
    }

    public function test_302_redirect_and_inactive_ignored(): void
    {
        Redirect::create(['from_path' => '/temp', 'to_url' => 'https://example.com', 'status_code' => 302]);
        Redirect::create(['from_path' => '/dead', 'to_url' => '/x', 'status_code' => 301, 'is_active' => false]);

        $this->get('/temp')->assertStatus(302)->assertRedirect('https://example.com');
        $this->get('/dead')->assertNotFound(); // falls through to catch-all → 404
    }

    public function test_redirect_admin_crud_requires_permission_and_normalises_paths(): void
    {
        $plain = User::factory()->create(['is_active' => true]);
        $this->actingAs($plain)->get('/cms/redirects')->assertForbidden();

        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/redirects', [
            'from_path' => 'old-collection/',
            'to_url' => 'collection/women',
            'status_code' => '301',
            'is_active' => 1,
        ])->assertRedirect();

        $redirect = Redirect::firstOrFail();
        $this->assertSame('/old-collection', $redirect->from_path);
        $this->assertSame('/collection/women', $redirect->to_url);

        // duplicate from_path (after normalisation) rejected
        $this->actingAs($admin)->post('/cms/redirects', [
            'from_path' => '/old-collection',
            'to_url' => '/elsewhere',
            'status_code' => '301',
        ])->assertSessionHasErrors('from_path');

        // toggle + delete
        $this->actingAs($admin)->post("/cms/redirects/{$redirect->id}/toggle")->assertRedirect();
        $this->assertFalse($redirect->fresh()->is_active);

        $this->actingAs($admin)->delete("/cms/redirects/{$redirect->id}")->assertRedirect();
        $this->assertSame(0, Redirect::count());
    }

    public function test_redirect_cache_busts_on_change(): void
    {
        Redirect::create(['from_path' => '/moved', 'to_url' => '/a', 'status_code' => 301]);
        $this->get('/moved')->assertRedirect('/a'); // primes cache

        Redirect::query()->first()->update(['to_url' => '/b']);
        $this->get('/moved')->assertRedirect('/b');
    }
}
