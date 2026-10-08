<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The builder's Live Preview is an iframe pointed at ?preview=1 — the same
 * route, controller and blades the public page uses. These tests hold that
 * line: if a future change gives preview its own rendering path, the editor
 * would start lying about what visitors see.
 */
class EditorLiveParityTest extends TestCase
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

    private function publishedPage(): Page
    {
        $page = Page::create([
            'title' => 'Parity',
            'slug' => 'parity',
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $page->sections()->create([
            'type' => 'hero',
            'name' => 'Hero',
            'sort_order' => 0,
            'is_active' => true,
            'data_desktop' => ['heading' => 'Parity heading', 'subheading' => 'Same both ways'],
            'style' => ['pt' => '40', 'custom_css' => '.parity{color:#123456}'],
        ]);

        return $page->refresh();
    }

    /**
     * The preview legitimately carries things a visitor never gets: the
     * inline-edit hooks and their assets. Parity is therefore about what the
     * page SAYS and what its sections ARE, not a byte match.
     */
    private function visibleText(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html);

        return trim(preg_replace('/\s+/', ' ', strip_tags($html)));
    }

    /** Every <section> opening tag: classes, inline styles, data-anim-*. */
    private function sectionTags(string $html): array
    {
        preg_match_all('/<section[^>]*>/i', $html, $matches);

        return array_map(
            fn (string $tag) => preg_replace('/\s+/', ' ', $tag),
            $matches[0]
        );
    }

    public function test_preview_and_published_render_the_same_page(): void
    {
        $this->publishedPage();

        $live = $this->get('/parity')->assertOk()->getContent();
        $preview = $this->actingAs($this->admin())->get('/parity?preview=1')->assertOk()->getContent();

        $this->assertSame(
            $this->visibleText($live),
            $this->visibleText($preview),
            'The builder preview must show exactly the text the published page shows.'
        );

        $this->assertSame(
            $this->sectionTags($live),
            $this->sectionTags($preview),
            'Section classes, styles and animation attributes must match between preview and live.'
        );
    }

    public function test_the_preview_adds_edit_hooks_and_the_public_page_does_not(): void
    {
        $this->publishedPage();

        $live = $this->get('/parity')->assertOk()->getContent();
        $preview = $this->actingAs($this->admin())->get('/parity?preview=1')->assertOk()->getContent();

        // The one intended difference, stated explicitly so a future change
        // that leaks these to visitors fails here.
        $this->assertStringContainsString('data-gsm-edit', $preview);
        $this->assertStringNotContainsString('data-gsm-edit', $live);
    }

    public function test_preview_shows_unpublished_edits_that_the_live_page_does_not(): void
    {
        $page = $this->publishedPage();

        // A draft page is the case where the two SHOULD differ: the editor can
        // see it, the public cannot.
        $page->update(['status' => Page::STATUS_DRAFT]);
        app(\App\Services\PageRenderService::class)->flushCache($page);

        $this->get('/parity')->assertNotFound();
        $this->actingAs($this->admin())->get('/parity?preview=1')->assertOk()->assertSee('Parity heading');
    }

    public function test_preview_is_not_available_to_visitors(): void
    {
        $page = $this->publishedPage();
        $page->update(['status' => Page::STATUS_DRAFT]);
        app(\App\Services\PageRenderService::class)->flushCache($page);

        // No session, and a logged-in user without pages.manage.
        $this->get('/parity?preview=1')->assertNotFound();

        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole(config('gsm.roles.viewer'));

        $this->actingAs($viewer)->get('/parity?preview=1')->assertNotFound();
    }

    public function test_section_styles_reach_both_renderings(): void
    {
        $this->publishedPage();

        foreach (['/parity', '/parity?preview=1'] as $url) {
            $html = $this->actingAs($this->admin())->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('.parity{color:#123456}', $html, "custom CSS missing from {$url}");
            $this->assertStringContainsString('Parity heading', $html, "content missing from {$url}");
        }
    }
}
