<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DeveloperOverride;
use App\Models\Page;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeveloperModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        Role::findOrCreate('Admin', 'web');
        Permission::findOrCreate('developer.mode', 'web');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function section(): Section
    {
        $page = Page::create([
            'title' => 'Dev Home', 'slug' => 'dev-home',
            'status' => Page::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        return $page->sections()->create([
            'type' => 'text-block',
            'sort_order' => 0,
            'data_desktop' => ['heading' => 'Original Heading', 'body' => '<p>Original body.</p>'],
        ]);
    }

    /* ── Permission gate ────────────────────────────────────────── */

    public function test_only_super_admin_can_access_developer_mode(): void
    {
        $section = $this->section();

        // Regular admin (has many permissions, but NOT developer.mode).
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('Admin');
        $this->actingAs($admin)->getJson("/cms/developer/sections/{$section->id}")->assertForbidden();

        $this->actingAs($this->superAdmin())
            ->getJson("/cms/developer/sections/{$section->id}")
            ->assertOk();
    }

    /* ── Modal payload ──────────────────────────────────────────── */

    public function test_payload_contains_generated_html_blade_path_and_component_info(): void
    {
        $section = $this->section();

        $payload = $this->actingAs($this->superAdmin())
            ->getJson("/cms/developer/sections/{$section->id}")
            ->assertOk()
            ->json();

        $this->assertFalse($payload['has_override']);
        $this->assertStringContainsString('Original Heading', $payload['original_html']);
        $this->assertSame($payload['original_html'], $payload['html']); // no override yet → original prefilled
        $this->assertSame('resources/views/frontend/sections/text-block.blade.php', $payload['info']['Blade file']);
        $this->assertSame(\App\Models\Section::class, $payload['info']['Model']);
        $this->assertSame('sections (overrides: developer_overrides)', $payload['info']['Database table']);
        $this->assertStringContainsString('SectionBuilderService', $payload['info']['Service']);
    }

    /* ── Override lifecycle ─────────────────────────────────────── */

    public function test_saved_override_renders_on_frontend_instead_of_original(): void
    {
        $section = $this->section();
        $dev = $this->superAdmin();

        $this->actingAs($dev)->putJson("/cms/developer/sections/{$section->id}", [
            'html' => '<div class="dev-custom">Overridden markup</div>',
            'css' => '.dev-custom{color:rebeccapurple;}',
            'js' => 'console.log("dev override");',
        ])->assertOk()->assertJsonPath('has_override', true);

        $html = $this->get('/dev-home')->assertOk()->getContent();

        $this->assertStringContainsString('Overridden markup', $html);
        $this->assertStringNotContainsString('Original Heading', $html);        // original replaced
        $this->assertStringContainsString('.dev-custom{color:rebeccapurple;}', $html);
        $this->assertStringContainsString('console.log("dev override");', $html);

        // The original Blade component file was never touched — reset brings it back.
        $this->actingAs($dev)->postJson("/cms/developer/sections/{$section->id}/reset")
            ->assertOk()->assertJsonPath('has_override', false);

        $html = $this->get('/dev-home')->assertOk()->getContent();
        $this->assertStringContainsString('Original Heading', $html);
        $this->assertStringNotContainsString('Overridden markup', $html);
    }

    public function test_versions_snapshot_on_save_and_restore(): void
    {
        $section = $this->section();
        $dev = $this->superAdmin();

        $this->actingAs($dev)->putJson("/cms/developer/sections/{$section->id}", ['html' => '<p>v1</p>']);
        $this->actingAs($dev)->putJson("/cms/developer/sections/{$section->id}", ['html' => '<p>v2</p>']);

        $override = DeveloperOverride::firstOrFail();
        $this->assertSame('<p>v2</p>', $override->html);
        $this->assertSame(1, $override->versions()->count()); // v1 snapshotted on second save

        $versionId = $override->versions()->first()->id;
        $payload = $this->actingAs($dev)
            ->postJson("/cms/developer/sections/{$section->id}/versions/{$versionId}/restore")
            ->assertOk()->json();

        $this->assertSame('<p>v1</p>', $payload['html']);
        $this->assertSame('<p>v1</p>', $override->fresh()->html);
        $this->assertSame(2, $override->versions()->count()); // v2 snapshotted before restore
    }

    public function test_preview_returns_sandboxed_document_without_saving(): void
    {
        $section = $this->section();

        $response = $this->actingAs($this->superAdmin())
            ->post("/cms/developer/sections/{$section->id}/preview", [
                'html' => '<h1>Preview only</h1>',
                'css' => 'h1{color:red;}',
                'js' => 'console.log(1);',
            ])->assertOk();

        $doc = $response->getContent();
        $this->assertStringContainsString('<h1>Preview only</h1>', $doc);
        $this->assertStringContainsString('h1{color:red;}', $doc);
        $this->assertStringContainsString('<!DOCTYPE html>', $doc);
        $this->assertSame(0, DeveloperOverride::count()); // nothing persisted
    }

    public function test_developer_mode_button_hidden_from_non_super_admins(): void
    {
        Permission::findOrCreate('builder.footer', 'web');
        Role::findOrCreate('Footer Editor', 'web')->givePermissionTo('builder.footer');

        $editor = User::factory()->create(['is_active' => true]);
        $editor->assignRole('Footer Editor');

        $this->seed(\Database\Seeders\SettingSeeder::class);
        $this->seed(\Database\Seeders\HeaderSeeder::class);
        $this->seed(\Database\Seeders\FooterSeeder::class);

        $this->actingAs($editor)->get('/cms/footer-builder')
            ->assertOk()
            ->assertDontSee('gsm-sc-devmode');

        $this->actingAs($this->superAdmin())->get('/cms/footer-builder')
            ->assertOk()
            ->assertSee('gsm-sc-devmode');
    }
}
