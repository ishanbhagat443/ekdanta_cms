<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\NotFoundLog;
use App\Models\Redirect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Final-integration gaps closed in this pass: DB-backed activity/login
 * logs (admin-visible, complementing the file-based 'security' channel),
 * a 404 Manager wired to the existing Redirect Manager, hardened default
 * password policy, and 2FA-ready schema (toggle only, per scope decision).
 */
class IntegrationHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        foreach (['activity.view', 'seo.manage'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    /* ── Activity / Login logs ────────────────────────────────────── */

    public function test_activity_log_requires_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/activity')->assertForbidden();
    }

    public function test_successful_login_recorded_in_activity_log(): void
    {
        $user = User::factory()->create(['is_active' => true, 'password' => bcrypt('correct-password')]);

        $this->post('/cms/login', ['email' => $user->email, 'password' => 'correct-password'])->assertRedirect();

        $log = ActivityLog::where('event', 'login')->firstOrFail();
        $this->assertSame($user->id, $log->user_id);
    }

    public function test_failed_login_recorded_in_activity_log(): void
    {
        $user = User::factory()->create(['is_active' => true, 'password' => bcrypt('correct-password')]);

        $this->post('/cms/login', ['email' => $user->email, 'password' => 'wrong-password']);

        $log = ActivityLog::where('event', 'login_failed')->firstOrFail();
        $this->assertSame($user->email, $log->description);
    }

    public function test_logout_recorded_in_activity_log(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/logout');

        $log = ActivityLog::where('event', 'logout')->firstOrFail();
        $this->assertSame($admin->id, $log->user_id);
    }

    public function test_activity_log_screen_filters_by_event_and_user(): void
    {
        $admin = $this->admin();
        ActivityLog::create(['user_id' => $admin->id, 'event' => 'login', 'ip' => '127.0.0.1']);
        ActivityLog::create(['user_id' => null, 'event' => 'login_failed', 'description' => 'ghost@example.com', 'ip' => '10.0.0.1']);

        $response = $this->actingAs($admin)->get('/cms/activity?event=login_failed')->assertOk();
        $response->assertSee('ghost@example.com');
        $response->assertDontSee('127.0.0.1');
    }

    /* ── 404 Manager ──────────────────────────────────────────────── */

    public function test_unknown_public_path_is_logged_as_not_found(): void
    {
        $this->get('/this-page-does-not-exist-anywhere')->assertNotFound();

        $log = NotFoundLog::firstOrFail();
        $this->assertSame('/this-page-does-not-exist-anywhere', $log->path);
        $this->assertSame(1, $log->hits);
    }

    public function test_repeated_404_increments_hit_count_not_duplicate_rows(): void
    {
        $this->get('/broken-link');
        $this->get('/broken-link');
        $this->get('/broken-link');

        $this->assertSame(1, NotFoundLog::count());
        $this->assertSame(3, NotFoundLog::firstOrFail()->hits);
    }

    public function test_admin_404_paths_are_not_logged(): void
    {
        $this->get('/cms/this-does-not-exist');

        $this->assertSame(0, NotFoundLog::count());
    }

    public function test_creating_matching_redirect_auto_resolves_the_404_log(): void
    {
        $admin = $this->admin();
        $this->get('/old-brochure');
        $log = NotFoundLog::firstOrFail();
        $this->assertFalse($log->resolved);

        $this->actingAs($admin)->post('/cms/redirects', [
            'from_path' => '/old-brochure', 'to_url' => '/brochure', 'status_code' => '301',
        ])->assertRedirect();

        $this->assertTrue($log->fresh()->resolved);
    }

    public function test_404_manager_requires_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/not-found')->assertForbidden();
    }

    public function test_dismiss_and_delete_not_found_log(): void
    {
        $admin = $this->admin();
        $log = NotFoundLog::create(['path' => '/gone', 'hits' => 1, 'last_hit_at' => now()]);

        $this->actingAs($admin)->post("/cms/not-found/{$log->id}/dismiss")->assertRedirect();
        $this->assertTrue($log->fresh()->resolved);

        $this->actingAs($admin)->delete("/cms/not-found/{$log->id}")->assertRedirect();
        $this->assertSame(0, NotFoundLog::count());
    }

    /* ── Password policy ──────────────────────────────────────────── */

    public function test_weak_password_rejected_by_hardened_default_policy(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/profile/password', [
            'current_password' => 'password',
            'password' => 'alllowercase', // no uppercase, no digit
            'password_confirmation' => 'alllowercase',
        ])->assertSessionHasErrors('password');
    }

    public function test_strong_password_accepted(): void
    {
        $admin = User::factory()->create(['is_active' => true, 'password' => bcrypt('OldPassw0rd')]);
        $admin->assignRole(config('gsm.roles.super_admin'));

        $this->actingAs($admin)->put('/cms/profile/password', [
            'current_password' => 'OldPassw0rd',
            'password' => 'NewStrongPass1',
            'password_confirmation' => 'NewStrongPass1',
        ])->assertRedirect()->assertSessionHasNoErrors();
    }

    /* ── 2FA-ready schema ─────────────────────────────────────────── */

    public function test_two_factor_columns_exist_and_are_hidden_from_serialization(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'two_factor_secret' => 'super-secret',
            'two_factor_recovery_codes' => json_encode(['abc123']),
        ]);

        $this->assertArrayNotHasKey('two_factor_secret', $user->toArray());
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $user->toArray());
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_profile_screen_shows_disabled_2fa_toggle(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/cms/profile')
            ->assertOk()
            ->assertSee('Two-Factor Authentication')
            ->assertSee('disabled', false);
    }

    /* ── Redirect auto-resolve doesn't error when no matching log exists ──── */

    public function test_redirect_save_without_matching_404_log_does_not_error(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/redirects', [
            'from_path' => '/never-hit', 'to_url' => '/somewhere', 'status_code' => '301',
        ])->assertRedirect();

        $this->assertSame(1, Redirect::count());
    }
}
