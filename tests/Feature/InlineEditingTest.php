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
 * Inline editing hooks belong to the builder preview and nobody else.
 * The behaviour itself is covered by the headless DOM harness; these tests
 * guard who receives the hooks at all.
 */
class InlineEditingTest extends TestCase
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

    private function page(): Page
    {
        $page = Page::create([
            'title' => 'Editable', 'slug' => 'editable',
            'status' => Page::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        $page->sections()->create([
            'type' => 'cards',
            'name' => 'Cards',
            'sort_order' => 0,
            'is_active' => true,
            'data_desktop' => ['heading' => 'Editable heading', 'subheading' => 'Editable sub'],
        ]);

        return $page->refresh();
    }

    public function test_the_preview_carries_edit_hooks(): void
    {
        $page = $this->page();

        $html = $this->actingAs($this->admin())
            ->get('/editable?preview=1')
            ->assertOk()
            ->getContent();

        $sectionId = $page->sections->first()->id;

        $this->assertStringContainsString('data-gsm-edit="'.$sectionId.':heading"', $html);
        $this->assertStringContainsString('data-gsm-edit="'.$sectionId.':subheading"', $html);
        $this->assertStringContainsString('gsm-inline-edit.js', $html);
        $this->assertStringContainsString('inline-edit.css', $html);
    }

    public function test_a_public_visitor_gets_none_of_it(): void
    {
        $this->page();

        $html = $this->get('/editable')->assertOk()->getContent();

        $this->assertStringNotContainsString('data-gsm-edit', $html);
        $this->assertStringNotContainsString('gsm-inline-edit.js', $html);
        $this->assertStringContainsString('Editable heading', $html, 'the content still renders');
    }

    public function test_a_signed_in_user_without_the_permission_gets_none_of_it(): void
    {
        $this->page();

        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole(config('gsm.roles.viewer'));

        $html = $this->actingAs($viewer)->get('/editable?preview=1')->assertOk()->getContent();

        $this->assertStringNotContainsString('data-gsm-edit', $html);
    }

    /**
     * Once a value differs per device there is no honest answer to "which one
     * am I editing?" on a single click, so those stay panel-only.
     */
    public function test_per_device_overrides_are_not_offered_inline(): void
    {
        $page = $this->page();

        $page->sections->first()->update([
            'data_mobile' => ['heading' => 'Shorter on phones'],
        ]);

        $html = $this->actingAs($this->admin())
            ->get('/editable?preview=1')
            ->assertOk()
            ->getContent();

        $sectionId = $page->sections->first()->id;

        $this->assertStringNotContainsString('data-gsm-edit="'.$sectionId.':heading"', $html);
        // …while a field that is still the same everywhere stays editable.
        $this->assertStringContainsString('data-gsm-edit="'.$sectionId.':subheading"', $html);
    }

    public function test_inline_hooks_do_not_break_editor_live_parity_for_visitors(): void
    {
        $this->page();

        // Two public renders must match: the hooks are keyed off the request,
        // not cached into the page.
        $first = $this->get('/editable')->assertOk()->getContent();
        $second = $this->get('/editable')->assertOk()->getContent();

        $strip = fn (string $html) => preg_replace('/<meta name="csrf-token"[^>]*>/', '', $html);

        $this->assertSame($strip($first), $strip($second));
        $this->assertStringNotContainsString('data-gsm-edit', $second);
    }
}
