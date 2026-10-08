<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (config('gsm.roles') as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        foreach (['settings.manage', 'users.view', 'users.create', 'users.update', 'users.delete', 'roles.manage'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function viewer(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.viewer'));

        return $user;
    }

    // ── Login / logout ────────────────────────────────────────────────

    public function test_login_page_renders(): void
    {
        $this->get('/cms/login')->assertOk();
    }

    public function test_valid_login_redirects_to_dashboard(): void
    {
        $user = $this->superAdmin();
        $user->update(['password' => Hash::make('secret-password')]);

        $this->post('/cms/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect('/cms/dashboard');

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_account_without_a_role_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret-password'),
            'is_active' => true,
        ]);

        $this->post('/cms/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_disabled_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret-password'),
            'is_active' => false,
        ]);

        $this->post('/cms/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_disabled_mid_session_is_logged_out(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)->get('/cms/dashboard')->assertOk();

        $user->update(['is_active' => false]);

        $this->actingAs($user)->get('/cms/dashboard')->assertRedirect(route('cms.login'));
        $this->assertGuest();
    }

    public function test_remember_me_sets_remember_token(): void
    {
        $user = $this->superAdmin();
        $user->update(['password' => Hash::make('secret-password'), 'remember_token' => null]);

        $this->post('/cms/login', [
            'email' => $user->email,
            'password' => 'secret-password',
            'remember' => 'on',
        ]);

        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_logout_ends_the_cms_session(): void
    {
        $staff = $this->superAdmin();

        $this->actingAs($staff, 'web')
            ->post('/cms/logout')
            ->assertRedirect(route('cms.login'));

        $this->assertGuest('web');
    }

    public function test_guest_is_redirected_from_admin(): void
    {
        $this->get('/cms/dashboard')->assertRedirect(route('cms.login'));
    }

    // ── Password reset ────────────────────────────────────────────────

    public function test_forgot_password_page_renders_and_sends_link(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $user = User::factory()->create(['is_active' => true]);

        $this->get('/password/reset')->assertOk();
        $this->post('/password/email', ['email' => $user->email])->assertSessionHas('status');

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\ResetPassword::class
        );
    }

    // ── Profile ───────────────────────────────────────────────────────

    public function test_profile_update(): void
    {
        $user = $this->viewer();

        $this->actingAs($user)
            ->put('/cms/profile', ['name' => 'New Name', 'email' => $user->email])
            ->assertRedirect(route('admin.profile.edit', absolute: false));

        $this->assertSame('New Name', $user->fresh()->name);
    }

    public function test_change_password_requires_current_password(): void
    {
        $user = $this->viewer();
        $user->update(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->put('/cms/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'BrandNewPass1',
                'password_confirmation' => 'BrandNewPass1',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->put('/cms/profile/password', [
                'current_password' => 'old-password',
                'password' => 'BrandNewPass1',
                'password_confirmation' => 'BrandNewPass1',
            ])
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('BrandNewPass1', $user->fresh()->password));
    }

    // ── User management ───────────────────────────────────────────────

    public function test_viewer_cannot_access_user_management(): void
    {
        $this->actingAs($this->viewer())->get('/cms/users')->assertForbidden();
    }

    public function test_super_admin_can_create_user_with_role(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/cms/users', [
                'name' => 'Content Person',
                'email' => 'content@example.com',
                'password' => 'SecretPassw0rd',
                'password_confirmation' => 'SecretPassw0rd',
                'role' => config('gsm.roles.content_manager'),
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index', absolute: false));

        $created = User::where('email', 'content@example.com')->firstOrFail();
        $this->assertTrue($created->hasRole(config('gsm.roles.content_manager')));
    }

    public function test_super_admin_cannot_delete_self(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->delete("/cms/users/{$admin->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    // ── Roles ─────────────────────────────────────────────────────────

    public function test_role_crud_with_permissions(): void
    {
        $admin = $this->superAdmin();

        // Create
        $this->actingAs($admin)->post('/cms/roles', [
            'name' => 'Catalog Team',
            'permissions' => ['users.view'],
        ])->assertRedirect(route('admin.roles.index', absolute: false));

        $role = Role::where('name', 'Catalog Team')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('users.view'));

        // Update
        $this->actingAs($admin)->put("/cms/roles/{$role->id}", [
            'name' => 'Catalog Crew',
            'permissions' => ['users.view', 'users.update'],
        ])->assertRedirect(route('admin.roles.index', absolute: false));

        $this->assertTrue($role->fresh()->hasPermissionTo('users.update'));

        // Delete
        $this->actingAs($admin)->delete("/cms/roles/{$role->id}")
            ->assertRedirect(route('admin.roles.index', absolute: false));

        $this->assertNull(Role::where('name', 'Catalog Crew')->first());
    }

    public function test_super_admin_role_cannot_be_edited_or_deleted(): void
    {
        $admin = $this->superAdmin();
        $superRole = Role::where('name', config('gsm.roles.super_admin'))->firstOrFail();

        $this->actingAs($admin)
            ->put("/cms/roles/{$superRole->id}", ['name' => 'Hacked'])
            ->assertSessionHasErrors('role');

        $this->actingAs($admin)
            ->delete("/cms/roles/{$superRole->id}")
            ->assertSessionHasErrors('role');

        $this->assertNotNull(Role::where('name', config('gsm.roles.super_admin'))->first());
    }

    public function test_role_assigned_to_users_cannot_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $viewerRole = Role::where('name', config('gsm.roles.viewer'))->firstOrFail();
        $this->viewer(); // attaches a user to Viewer

        $this->actingAs($admin)
            ->delete("/cms/roles/{$viewerRole->id}")
            ->assertSessionHasErrors('role');
    }
}
