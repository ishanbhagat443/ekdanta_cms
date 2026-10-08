<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Section;
use App\Models\User;
use App\Services\PageRevisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Version history: snapshot on save, restore, preview, pruning.
 */
class PageRevisionTest extends TestCase
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

    private function page(string $slug = 'about-us'): Page
    {
        $page = Page::create([
            'title' => 'About Us',
            'slug' => $slug,
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $page->sections()->create([
            'type' => 'hero',
            'name' => 'Hero',
            'sort_order' => 0,
            'is_active' => true,
            'data_desktop' => ['heading' => 'Original heading', 'alignment' => 'center'],
        ]);

        return $page->refresh();
    }

    private function service(): PageRevisionService
    {
        return app(PageRevisionService::class);
    }

    private function saveForm(User $user, Page $page, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)->put("/cms/pages/{$page->id}", array_merge([
            'title' => $page->title,
            'slug' => $page->slug,
            'status' => $page->status,
        ], $overrides));
    }

    /* ── Capture ─────────────────────────────────────────────────── */

    public function test_saving_a_page_captures_a_revision(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        $this->assertDatabaseCount('page_revisions', 0);

        $this->saveForm($admin, $page, ['title' => 'About Our Studio'])->assertRedirect();

        $this->assertDatabaseCount('page_revisions', 1);

        $revision = PageRevision::firstOrFail();
        $this->assertSame($page->id, $revision->page_id);
        $this->assertSame($admin->id, $revision->user_id);
        $this->assertSame('About Our Studio', $revision->data['page']['title']);
        $this->assertSame('Original heading', $revision->data['sections'][0]['data_desktop']['heading']);
    }

    public function test_an_unchanged_save_does_not_add_a_duplicate_revision(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        $this->saveForm($admin, $page);
        $this->saveForm($admin, $page);
        $this->saveForm($admin, $page);

        $this->assertDatabaseCount('page_revisions', 1);
    }

    public function test_history_is_pruned_to_the_retention_limit(): void
    {
        $page = $this->page();
        $service = $this->service();

        for ($i = 0; $i < PageRevisionService::KEEP_PER_PAGE + 8; $i++) {
            $page->update(['title' => "Title {$i}"]);
            $service->capture($page->refresh(), null);
        }

        $this->assertSame(PageRevisionService::KEEP_PER_PAGE, PageRevision::where('page_id', $page->id)->count());

        // The survivors are the newest ones.
        $newest = PageRevision::where('page_id', $page->id)->latest('id')->first();
        $this->assertSame('Title '.(PageRevisionService::KEEP_PER_PAGE + 7), $newest->data['page']['title']);
    }

    /* ── Restore ─────────────────────────────────────────────────── */

    public function test_restoring_brings_back_the_old_content(): void
    {
        $page = $this->page();
        $admin = $this->admin();
        $service = $this->service();

        $original = $service->capture($page, $admin->id);

        // …then the editor changes things and adds a section.
        $section = $page->sections()->first();
        $section->update(['data_desktop' => ['heading' => 'Rewritten heading']]);
        $page->sections()->create(['type' => 'cta', 'name' => 'Later CTA', 'sort_order' => 1, 'is_active' => true]);
        $page->update(['title' => 'Renamed']);

        $this->actingAs($admin)
            ->post("/cms/pages/{$page->id}/revisions/{$original->id}/restore")
            ->assertRedirect(route('admin.pages.edit', $page));

        $page->refresh();

        $this->assertSame('About Us', $page->title);
        $this->assertCount(1, $page->sections, 'the section added later is gone');
        $this->assertSame('Original heading', $page->sections->first()->data_desktop['heading']);
    }

    public function test_restoring_keeps_section_ids_so_references_survive(): void
    {
        $page = $this->page();
        $admin = $this->admin();
        $sectionId = $page->sections->first()->id;

        $revision = $this->service()->capture($page, $admin->id);

        $page->sections()->first()->update(['data_desktop' => ['heading' => 'Changed']]);

        $this->actingAs($admin)->post("/cms/pages/{$page->id}/revisions/{$revision->id}/restore");

        $this->assertSame($sectionId, $page->refresh()->sections->first()->id);
    }

    public function test_restoring_a_deleted_section_recreates_it(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        $revision = $this->service()->capture($page, $admin->id);

        $page->sections()->delete();
        $this->assertCount(0, $page->refresh()->sections);

        $this->actingAs($admin)->post("/cms/pages/{$page->id}/revisions/{$revision->id}/restore");

        $sections = $page->refresh()->sections;
        $this->assertCount(1, $sections);
        $this->assertSame('Original heading', $sections->first()->data_desktop['heading']);
    }

    public function test_the_state_before_a_restore_is_itself_saved(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        $revision = $this->service()->capture($page, $admin->id);
        $page->sections()->first()->update(['data_desktop' => ['heading' => 'About to be undone']]);

        $this->actingAs($admin)->post("/cms/pages/{$page->id}/revisions/{$revision->id}/restore");

        $safety = PageRevision::where('label', 'Before restore')->firstOrFail();
        $this->assertSame('About to be undone', $safety->data['sections'][0]['data_desktop']['heading']);
    }

    public function test_the_newest_version_after_a_restore_is_the_restored_one(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        $original = $this->service()->capture($page, $admin->id);

        $page->sections()->first()->update(['data_desktop' => ['heading' => 'Newer heading']]);

        $this->actingAs($admin)->post("/cms/pages/{$page->id}/revisions/{$original->id}/restore");

        // The history panel badges the newest row "Current", so it must hold
        // what the page now shows — not the pre-restore content.
        $newest = \App\Models\PageRevision::where('page_id', $page->id)->latest('id')->firstOrFail();

        $this->assertSame('Original heading', $newest->data['sections'][0]['data_desktop']['heading']);
        $this->assertSame('Restored', $newest->label);

        // And the pre-restore state is still recoverable, one row down.
        $this->assertTrue(
            \App\Models\PageRevision::where('page_id', $page->id)
                ->where('label', 'Before restore')
                ->exists()
        );
    }

    /* ── Preview ─────────────────────────────────────────────────── */

    public function test_previewing_a_revision_renders_it_without_writing(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        $revision = $this->service()->capture($page, $admin->id);
        $page->sections()->first()->update(['data_desktop' => ['heading' => 'Live heading now']]);

        $this->actingAs($admin)
            ->get("/cms/pages/{$page->id}/revisions/{$revision->id}/preview")
            ->assertOk()
            ->assertSee('Original heading')
            ->assertDontSee('Live heading now');

        // The live row is untouched by the preview.
        $this->assertSame('Live heading now', $page->refresh()->sections->first()->data_desktop['heading']);
    }

    /* ── Access control ──────────────────────────────────────────── */

    public function test_history_requires_the_pages_permission(): void
    {
        $page = $this->page();
        $revision = $this->service()->capture($page, null);

        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole(config('gsm.roles.viewer'));

        $this->actingAs($viewer)->get("/cms/pages/{$page->id}/revisions")->assertForbidden();
        $this->actingAs($viewer)->get("/cms/pages/{$page->id}/revisions/{$revision->id}/preview")->assertForbidden();
        $this->actingAs($viewer)->post("/cms/pages/{$page->id}/revisions/{$revision->id}/restore")->assertForbidden();
    }

    public function test_a_revision_from_another_page_is_not_addressable(): void
    {
        $page = $this->page('about-us');
        $other = $this->page('services');
        $admin = $this->admin();

        $otherRevision = $this->service()->capture($other, $admin->id);

        $this->actingAs($admin)
            ->get("/cms/pages/{$page->id}/revisions/{$otherRevision->id}/preview")
            ->assertNotFound();

        $this->actingAs($admin)
            ->post("/cms/pages/{$page->id}/revisions/{$otherRevision->id}/restore")
            ->assertNotFound();
    }

    /* ── Autosave ────────────────────────────────────────────────── */

    public function test_autosave_does_not_write_version_history(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        // One explicit save establishes the baseline entry…
        $this->saveForm($admin, $page, ['title' => 'Explicit'])->assertRedirect();
        $this->assertDatabaseCount('page_revisions', 1);

        // …then six autosaves must not add any. Otherwise the retention
        // window fills with keystroke noise and evicts the real snapshots.
        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($admin)->putJson("/cms/pages/{$page->id}", [
                'title' => "Draft {$i}",
                'slug' => $page->slug,
                'status' => $page->status,
                'autosave' => 1,
            ])->assertOk();
        }

        $this->assertDatabaseCount('page_revisions', 1);
        $this->assertSame('Draft 5', $page->refresh()->title, 'autosave still persists the edit');
    }

    public function test_restoring_a_slug_that_was_taken_does_not_error(): void
    {
        $page = $this->page('about-us');
        $admin = $this->admin();

        $revision = $this->service()->capture($page, $admin->id);

        // The page is renamed, and something else claims the old slug.
        $page->update(['slug' => 'about-the-studio']);
        Page::create(['title' => 'Squatter', 'slug' => 'about-us', 'status' => Page::STATUS_DRAFT]);

        $this->actingAs($admin)
            ->post("/cms/pages/{$page->id}/revisions/{$revision->id}/restore")
            ->assertRedirect();

        // De-duplicated rather than colliding on the unique index.
        $this->assertNotSame('about-us', $page->refresh()->slug);
        $this->assertDatabaseHas('pages', ['slug' => 'about-us', 'title' => 'Squatter']);
    }

    public function test_restoring_a_slug_change_does_not_leave_a_stale_page_cached(): void
    {
        $page = $this->page('original-slug');
        $admin = $this->admin();

        $revision = $this->service()->capture($page, $admin->id);

        // Visit it so the render cache is warm under the old slug.
        $this->get('/original-slug')->assertOk();

        $page->update(['slug' => 'renamed-slug']);
        app(\App\Services\PageService::class)->updatePage($page, [
            'title' => $page->title, 'slug' => 'renamed-slug', 'status' => Page::STATUS_PUBLISHED,
        ]);

        $this->get('/renamed-slug')->assertOk();

        // Restoring puts the old slug back; the renamed URL must stop serving.
        $this->actingAs($admin)->post("/cms/pages/{$page->id}/revisions/{$revision->id}/restore");

        $this->get('/original-slug')->assertOk();
        $this->get('/renamed-slug')->assertNotFound();
    }

    public function test_a_failed_restore_leaves_no_orphan_revision(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        $revision = $this->service()->capture($page, $admin->id);
        $page->sections()->first()->update(['data_desktop' => ['heading' => 'Changed since']]);

        $before = \App\Models\PageRevision::count();

        // Make the write half of the restore fail, after the safety snapshot
        // has been taken. Engine-independent: no reliance on a column limit
        // that SQLite silently ignores.
        $this->app->bind(\App\Services\PageService::class, function () {
            return new class(app(\App\Repositories\Contracts\PageRepositoryInterface::class), app(\App\Services\PageRenderService::class)) extends \App\Services\PageService
            {
                public function updatePage(Page $page, array $data): Page
                {
                    throw new \RuntimeException('restore blew up');
                }
            };
        });

        try {
            app(PageRevisionService::class)->restore($page->refresh(), $revision, $admin->id);
            $this->fail('the restore was supposed to throw');
        } catch (\RuntimeException $e) {
            $this->assertSame('restore blew up', $e->getMessage());
        }

        // The "Before restore" snapshot rolled back with the transaction
        // instead of being left behind as a phantom version.
        $this->assertSame($before, \App\Models\PageRevision::count());
        $this->assertSame(0, \App\Models\PageRevision::where('label', 'Before restore')->count());
    }

    public function test_autosave_gets_a_json_answer(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->putJson("/cms/pages/{$page->id}", [
                'title' => 'Autosaved title',
                'slug' => $page->slug,
                'status' => $page->status,
            ])
            ->assertOk()
            ->assertJson(['saved' => true])
            ->assertJsonStructure(['saved', 'saved_at', 'page' => ['slug', 'status']]);

        $this->assertSame('Autosaved title', $page->refresh()->title);
    }

    public function test_revisions_are_removed_with_their_page(): void
    {
        $page = $this->page();
        $this->service()->capture($page, null);

        $this->assertDatabaseCount('page_revisions', 1);

        $page->forceDelete();

        $this->assertDatabaseCount('page_revisions', 0);
    }
}
