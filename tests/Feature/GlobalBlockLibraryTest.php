<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GlobalBlock;
use App\Models\GlobalBlockCategory;
use App\Models\GlobalBlockVersion;
use App\Models\Page;
use App\Models\Section;
use App\Models\User;
use App\Services\PageRenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GlobalBlockLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        foreach (['blocks.manage', 'pages.manage'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function publishedPage(string $slug = 'home'): Page
    {
        return Page::create(['title' => 'Home', 'slug' => $slug, 'status' => Page::STATUS_PUBLISHED, 'published_at' => now()]);
    }

    /* ── Access + library screen ──────────────────────────────────── */

    public function test_block_library_requires_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/global-blocks')->assertForbidden();
    }

    public function test_create_block_from_a_section_type(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/global-blocks', [
            'name' => 'Homepage Hero', 'type' => 'hero',
        ])->assertRedirect();

        $block = GlobalBlock::where('name', 'Homepage Hero')->firstOrFail();
        $this->assertSame('hero', $block->type);
        $this->assertSame(1, $block->version);
        $this->assertSame($admin->id, $block->created_by);
        $this->assertNotEmpty($block->slug);
    }

    public function test_unknown_section_type_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/global-blocks', [
            'name' => 'Bad Block', 'type' => 'not-a-real-type',
        ])->assertSessionHasErrors('type');
    }

    /* ── Editing the master ───────────────────────────────────────── */

    public function test_update_master_desktop_payload_validates_and_bumps_version(): void
    {
        $admin = $this->admin();
        $block = GlobalBlock::create(['name' => 'CTA Block', 'slug' => 'cta-block', 'type' => 'cta', 'version' => 1, 'created_by' => $admin->id]);

        $this->actingAs($admin)->putJson("/cms/global-blocks/{$block->id}", [
            'device' => 'desktop',
            'data' => ['heading' => 'Get In Touch', 'subheading' => 'We reply within 24 hours'],
        ])->assertOk();

        $block->refresh();
        $this->assertSame('Get In Touch', $block->data_desktop['heading']);
        $this->assertSame(2, $block->version);
    }

    public function test_first_save_writes_a_version_snapshot_of_the_prior_state(): void
    {
        $admin = $this->admin();
        $block = GlobalBlock::create([
            'name' => 'CTA Block', 'slug' => 'cta-block', 'type' => 'cta', 'version' => 1,
            'created_by' => $admin->id, 'data_desktop' => ['heading' => 'Original'],
        ]);

        $this->actingAs($admin)->putJson("/cms/global-blocks/{$block->id}", [
            'device' => 'desktop', 'data' => ['heading' => 'Updated'],
        ])->assertOk();

        $snapshot = GlobalBlockVersion::where('global_block_id', $block->id)->where('version', 1)->firstOrFail();
        $this->assertSame('Original', $snapshot->data_desktop['heading']);
    }

    public function test_tablet_save_only_stores_device_overridable_keys(): void
    {
        $admin = $this->admin();
        $block = GlobalBlock::create(['name' => 'Hero Block', 'slug' => 'hero-block', 'type' => 'hero', 'version' => 1, 'created_by' => $admin->id]);

        $this->actingAs($admin)->putJson("/cms/global-blocks/{$block->id}", [
            'device' => 'tablet',
            'data' => ['heading' => 'Tablet Heading', 'overlay_opacity' => 80], // overlay_opacity is NOT device-overridable
        ])->assertOk();

        $block->refresh();
        $this->assertSame('Tablet Heading', $block->data_tablet['heading']);
        $this->assertArrayNotHasKey('overlay_opacity', $block->data_tablet ?? []);
    }

    /* ── Placing a block on a page + Update All Pages / Detach ───── */

    public function test_place_block_on_page_creates_linked_section_and_tracks_usage(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();
        $block = GlobalBlock::create([
            'name' => 'CTA Block', 'slug' => 'cta-block', 'type' => 'cta', 'version' => 1,
            'created_by' => $admin->id, 'status' => 'active', 'data_desktop' => ['heading' => 'Original CTA'],
        ]);

        $this->actingAs($admin)->postJson('/cms/sections/from-block', [
            'area' => 'page', 'page_id' => $page->id, 'global_block_id' => $block->id,
        ])->assertCreated();

        $section = Section::where('sectionable_id', $page->id)->where('sectionable_type', Page::class)->firstOrFail();
        $this->assertSame($block->id, $section->global_block_id);
        $this->assertTrue($section->is_synced);
        $this->assertSame('Original CTA', $section->data_desktop['heading']);

        $block->refresh();
        $this->assertSame(1, $block->use_count);
        $this->assertNotNull($block->last_used_at);
    }

    public function test_inactive_block_cannot_be_placed_on_a_page(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();
        $block = GlobalBlock::create(['name' => 'Draft Block', 'slug' => 'draft-block', 'type' => 'cta', 'version' => 1, 'status' => 'draft']);

        $this->actingAs($admin)->postJson('/cms/sections/from-block', [
            'area' => 'page', 'page_id' => $page->id, 'global_block_id' => $block->id,
        ])->assertUnprocessable();
    }

    public function test_update_all_pages_pushes_new_payload_to_every_synced_instance(): void
    {
        $admin = $this->admin();
        $pageA = $this->publishedPage('page-a');
        $pageB = $this->publishedPage('page-b');
        $block = GlobalBlock::create([
            'name' => 'CTA Block', 'slug' => 'cta-block', 'type' => 'cta', 'version' => 1,
            'created_by' => $admin->id, 'status' => 'active', 'data_desktop' => ['heading' => 'V1'],
        ]);

        $this->actingAs($admin)->postJson('/cms/sections/from-block', ['area' => 'page', 'page_id' => $pageA->id, 'global_block_id' => $block->id]);
        $this->actingAs($admin)->postJson('/cms/sections/from-block', ['area' => 'page', 'page_id' => $pageB->id, 'global_block_id' => $block->id]);

        $this->actingAs($admin)->putJson("/cms/global-blocks/{$block->id}", [
            'device' => 'desktop', 'data' => ['heading' => 'V2 Updated'], 'propagate' => true,
        ])->assertOk();

        $sections = Section::where('global_block_id', $block->id)->get();
        $this->assertCount(2, $sections);
        foreach ($sections as $section) {
            $this->assertSame('V2 Updated', $section->fresh()->data_desktop['heading']);
        }
    }

    public function test_propagate_false_updates_master_only_not_instances(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();
        $block = GlobalBlock::create([
            'name' => 'CTA Block', 'slug' => 'cta-block', 'type' => 'cta', 'version' => 1,
            'created_by' => $admin->id, 'status' => 'active', 'data_desktop' => ['heading' => 'V1'],
        ]);
        $this->actingAs($admin)->postJson('/cms/sections/from-block', ['area' => 'page', 'page_id' => $page->id, 'global_block_id' => $block->id]);

        $this->actingAs($admin)->putJson("/cms/global-blocks/{$block->id}", [
            'device' => 'desktop', 'data' => ['heading' => 'Master Only Update'], 'propagate' => false,
        ])->assertOk();

        $section = Section::where('global_block_id', $block->id)->firstOrFail();
        $this->assertSame('V1', $section->data_desktop['heading']);
        $this->assertSame('Master Only Update', $block->fresh()->data_desktop['heading']);
    }

    public function test_detach_freezes_instance_and_further_master_updates_do_not_affect_it(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();
        $block = GlobalBlock::create([
            'name' => 'CTA Block', 'slug' => 'cta-block', 'type' => 'cta', 'version' => 1,
            'created_by' => $admin->id, 'status' => 'active', 'data_desktop' => ['heading' => 'V1'],
        ]);
        $this->actingAs($admin)->postJson('/cms/sections/from-block', ['area' => 'page', 'page_id' => $page->id, 'global_block_id' => $block->id]);
        $section = Section::where('global_block_id', $block->id)->firstOrFail();

        $this->actingAs($admin)->postJson("/cms/sections/{$section->id}/detach")->assertOk();
        $this->assertFalse($section->fresh()->is_synced);

        $this->actingAs($admin)->putJson("/cms/global-blocks/{$block->id}", [
            'device' => 'desktop', 'data' => ['heading' => 'V2 After Detach'], 'propagate' => true,
        ])->assertOk();

        $this->assertSame('V1', $section->fresh()->data_desktop['heading']);
        // Link is remembered for audit purposes even though it's detached.
        $this->assertSame($block->id, $section->fresh()->global_block_id);
    }

    public function test_reattach_pulls_current_master_payload(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();
        $block = GlobalBlock::create([
            'name' => 'CTA Block', 'slug' => 'cta-block', 'type' => 'cta', 'version' => 1,
            'created_by' => $admin->id, 'status' => 'active', 'data_desktop' => ['heading' => 'Master Now'],
        ]);
        $section = $page->sections()->create([
            'type' => 'cta', 'data_desktop' => ['heading' => 'Locally Edited'], 'global_block_id' => null, 'is_synced' => false,
        ]);

        $this->actingAs($admin)->postJson("/cms/sections/{$section->id}/reattach", ['global_block_id' => $block->id])->assertOk();

        $section->refresh();
        $this->assertTrue($section->is_synced);
        $this->assertSame($block->id, $section->global_block_id);
        $this->assertSame('Master Now', $section->data_desktop['heading']);
    }

    /* ── Promote a local section to a global block ───────────────── */

    public function test_promote_section_creates_master_and_links_original_section(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();
        $section = $page->sections()->create(['type' => 'cta', 'data_desktop' => ['heading' => 'Promote Me'], 'sort_order' => 0]);

        $response = $this->actingAs($admin)->postJson("/cms/sections/{$section->id}/promote", [
            'name' => 'Promoted CTA',
        ])->assertCreated();

        $block = GlobalBlock::where('name', 'Promoted CTA')->firstOrFail();
        $this->assertSame('Promote Me', $block->data_desktop['heading']);
        $this->assertSame($block->id, $section->fresh()->global_block_id);
        $this->assertTrue($section->fresh()->is_synced);
    }

    /* ── Delete master: instances survive as local copies ─────────── */

    public function test_deleting_master_leaves_instances_as_local_sections(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();
        $block = GlobalBlock::create([
            'name' => 'CTA Block', 'slug' => 'cta-block', 'type' => 'cta', 'version' => 1,
            'created_by' => $admin->id, 'status' => 'active', 'data_desktop' => ['heading' => 'Kept Content'],
        ]);
        $this->actingAs($admin)->postJson('/cms/sections/from-block', ['area' => 'page', 'page_id' => $page->id, 'global_block_id' => $block->id]);
        $section = Section::where('global_block_id', $block->id)->firstOrFail();

        $this->actingAs($admin)->delete("/cms/global-blocks/{$block->id}")->assertRedirect();

        $this->assertSame(0, GlobalBlock::count());
        $section->refresh();
        $this->assertNull($section->global_block_id);
        $this->assertFalse($section->is_synced);
        $this->assertSame('Kept Content', $section->data_desktop['heading']); // content survives
    }

    /* ── Categories, favorites, search, sort ──────────────────────── */

    public function test_categorize_search_and_favorite_a_block(): void
    {
        $admin = $this->admin();
        $category = GlobalBlockCategory::create(['name' => 'Hero', 'slug' => 'hero']);
        $block = GlobalBlock::create([
            'name' => 'Findable Block', 'slug' => 'findable-block', 'type' => 'cta',
            'category_id' => $category->id, 'version' => 1,
        ]);
        GlobalBlock::create(['name' => 'Other Block', 'slug' => 'other-block', 'type' => 'faq', 'version' => 1]);

        $response = $this->actingAs($admin)->get('/cms/global-blocks?search=Findable')->assertOk();
        $response->assertSee('Findable Block')->assertDontSee('Other Block');

        $this->actingAs($admin)->postJson("/cms/global-blocks/{$block->id}/favorite")
            ->assertOk()->assertJson(['is_favorite' => true]);
        $this->assertTrue($block->fresh()->is_favorite);

        $response = $this->actingAs($admin)->get('/cms/global-blocks?favorites=1')->assertOk();
        $response->assertSee('Findable Block')->assertDontSee('Other Block');
    }

    public function test_most_used_and_recently_used_sort(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();

        $popular = GlobalBlock::create(['name' => 'Popular Block', 'slug' => 'popular-block', 'type' => 'cta', 'status' => 'active', 'version' => 1, 'use_count' => 5, 'last_used_at' => now()]);
        $rare = GlobalBlock::create(['name' => 'Rare Block', 'slug' => 'rare-block', 'type' => 'cta', 'status' => 'active', 'version' => 1, 'use_count' => 1, 'last_used_at' => now()->subDays(10)]);

        $response = $this->actingAs($admin)->get('/cms/global-blocks?sort=most_used')->assertOk();
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Popular Block') < strpos($content, 'Rare Block'));
    }

    /* ── Duplicate ─────────────────────────────────────────────────── */

    public function test_duplicate_block_creates_independent_draft_copy(): void
    {
        $admin = $this->admin();
        $block = GlobalBlock::create([
            'name' => 'Original', 'slug' => 'original', 'type' => 'cta', 'version' => 3,
            'status' => 'active', 'data_desktop' => ['heading' => 'Shared Content'],
        ]);

        $this->actingAs($admin)->post("/cms/global-blocks/{$block->id}/duplicate")->assertRedirect();

        $copy = GlobalBlock::where('name', 'Original (Copy)')->firstOrFail();
        $this->assertSame('draft', $copy->status);
        $this->assertSame(1, $copy->version);
        $this->assertSame('Shared Content', $copy->data_desktop['heading']);
        $this->assertNotSame($block->id, $copy->id);
    }

    /* ── Version history + restore ────────────────────────────────── */

    public function test_restore_previous_version_pushes_to_linked_pages(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage();
        $block = GlobalBlock::create([
            'name' => 'CTA Block', 'slug' => 'cta-block', 'type' => 'cta', 'version' => 1,
            'created_by' => $admin->id, 'status' => 'active', 'data_desktop' => ['heading' => 'Original'],
        ]);
        $this->actingAs($admin)->postJson('/cms/sections/from-block', ['area' => 'page', 'page_id' => $page->id, 'global_block_id' => $block->id]);

        $this->actingAs($admin)->putJson("/cms/global-blocks/{$block->id}", [
            'device' => 'desktop', 'data' => ['heading' => 'Changed'],
        ])->assertOk();

        $version = GlobalBlockVersion::where('global_block_id', $block->id)->where('version', 1)->firstOrFail();

        $this->actingAs($admin)->post("/cms/global-blocks/{$block->id}/versions/{$version->id}/restore")->assertRedirect();

        $this->assertSame('Original', $block->fresh()->data_desktop['heading']);
        $section = Section::where('global_block_id', $block->id)->firstOrFail();
        $this->assertSame('Original', $section->data_desktop['heading']);
    }

    /* ── Export / Import ──────────────────────────────────────────── */

    public function test_export_and_reimport_round_trip(): void
    {
        $admin = $this->admin();
        $block = GlobalBlock::create([
            'name' => 'Exportable', 'slug' => 'exportable', 'type' => 'faq', 'version' => 1,
            'data_desktop' => ['heading' => 'FAQ Heading'],
        ]);

        $json = $this->actingAs($admin)->get("/cms/global-blocks/{$block->id}/export")->assertOk()->streamedContent();
        $payload = json_decode($json, true);
        $this->assertSame('faq', $payload['type']);
        $this->assertSame('FAQ Heading', $payload['data_desktop']['heading']);

        $file = UploadedFile::fake()->createWithContent('block.json', json_encode($payload));
        $this->actingAs($admin)->post('/cms/global-blocks/import', ['file' => $file])->assertRedirect();

        $imported = GlobalBlock::where('name', 'Exportable')->where('status', 'draft')->latest('id')->firstOrFail();
        $this->assertSame('FAQ Heading', $imported->data_desktop['heading']);
    }

    public function test_import_rejects_file_with_unknown_type(): void
    {
        $admin = $this->admin();
        $file = UploadedFile::fake()->createWithContent('bad.json', json_encode(['type' => 'not-real', 'data_desktop' => []]));

        $this->actingAs($admin)->post('/cms/global-blocks/import', ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    /* ── Cache invalidation ───────────────────────────────────────── */

    public function test_page_render_cache_flushed_when_linked_block_updates(): void
    {
        $admin = $this->admin();
        $page = $this->publishedPage('cache-test-page');
        $block = GlobalBlock::create([
            'name' => 'CTA Block', 'slug' => 'cta-block', 'type' => 'cta', 'version' => 1,
            'status' => 'active', 'data_desktop' => ['heading' => 'Cached V1'],
        ]);
        $this->actingAs($admin)->postJson('/cms/sections/from-block', ['area' => 'page', 'page_id' => $page->id, 'global_block_id' => $block->id]);

        // Prime the page-render cache.
        app(PageRenderService::class)->payloadFor('cache-test-page');

        $this->actingAs($admin)->putJson("/cms/global-blocks/{$block->id}", [
            'device' => 'desktop', 'data' => ['heading' => 'Cached V2'],
        ])->assertOk();

        $payload = app(PageRenderService::class)->payloadFor('cache-test-page');
        $renderedSection = $payload['sections']->first()['section'];
        $this->assertSame('Cached V2', $renderedSection->data_desktop['heading']);
    }

    /* ── Preview endpoint ──────────────────────────────────────────── */

    public function test_preview_renders_block_via_real_frontend_pipeline(): void
    {
        $admin = $this->admin();
        $block = GlobalBlock::create([
            'name' => 'Preview Block', 'slug' => 'preview-block', 'type' => 'cta', 'version' => 1,
            'data_desktop' => ['heading' => 'Preview Heading Text'],
        ]);

        $this->actingAs($admin)->get("/cms/global-blocks/{$block->id}/preview")
            ->assertOk()
            ->assertSee('Preview Heading Text');
    }
}
