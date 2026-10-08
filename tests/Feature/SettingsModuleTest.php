<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsModuleTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        Permission::findOrCreate('settings.manage', 'web');

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    public function test_settings_page_requires_permission(): void
    {
        Permission::findOrCreate('settings.manage', 'web');
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/settings/business')->assertForbidden();
    }

    public function test_unknown_group_returns_404(): void
    {
        $this->actingAs($this->superAdmin())->get('/cms/settings/nonsense')->assertNotFound();
    }

    public function test_business_settings_can_be_saved_and_read_via_helper(): void
    {
        $this->actingAs($this->superAdmin())
            ->put('/cms/settings/business', [
                'company_name' => 'Acme Studio',
                'email' => 'globalsilkemb@gmail.com',
                'latitude' => '21.0806',
                'longitude' => '72.8811',
            ])
            ->assertRedirect('/cms/settings/business')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'group' => 'business',
            'key' => 'company_name',
            'value' => 'Acme Studio',
        ]);

        $this->assertSame('globalsilkemb@gmail.com', setting('business.email'));
        $this->assertSame('fallback', setting('business.missing_key', 'fallback'));
    }

    public function test_invalid_values_are_rejected(): void
    {
        $this->actingAs($this->superAdmin())
            ->from('/cms/settings/business')
            ->put('/cms/settings/business', [
                'email' => 'not-an-email',
                'latitude' => '999',
            ])
            ->assertRedirect('/cms/settings/business')
            ->assertSessionHasErrors(['email', 'latitude']);
    }

    public function test_branding_logo_upload_replace_and_remove(): void
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        // Upload
        $this->actingAs($admin)->put('/cms/settings/branding', [
            'logo_dark' => UploadedFile::fake()->image('logo.png', 300, 100),
        ])->assertRedirect();

        $path = setting('branding.logo_dark');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        // Replace deletes old file
        $this->actingAs($admin)->put('/cms/settings/branding', [
            'logo_dark' => UploadedFile::fake()->image('logo2.png', 300, 100),
        ])->assertRedirect();

        Storage::disk('public')->assertMissing($path);
        $newPath = setting('branding.logo_dark');
        Storage::disk('public')->assertExists($newPath);

        // Remove checkbox clears value and deletes file
        $this->actingAs($admin)->put('/cms/settings/branding', [
            'remove' => ['logo_dark' => '1'],
        ])->assertRedirect();

        Storage::disk('public')->assertMissing($newPath);
        $this->assertNull(setting('branding.logo_dark'));
    }

    public function test_cache_is_flushed_on_update(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put('/cms/settings/business', ['phone' => '111']);
        $this->assertSame('111', setting('business.phone'));

        $this->actingAs($admin)->put('/cms/settings/business', ['phone' => '222']);
        $this->assertSame('222', setting('business.phone'));
    }

    public function test_invalid_schema_json_is_rejected(): void
    {
        $this->actingAs($this->superAdmin())
            ->from('/cms/settings/seo')
            ->put('/cms/settings/seo', ['schema_json' => '{not json'])
            ->assertSessionHasErrors(['schema_json']);
    }

    public function test_settings_page_renders(): void
    {
        app(SettingsService::class)->flushCache();

        $this->actingAs($this->superAdmin())
            ->get('/cms/settings/business')
            ->assertOk()
            ->assertSee('Business Information')
            ->assertSee('Working Hours');
    }
}
