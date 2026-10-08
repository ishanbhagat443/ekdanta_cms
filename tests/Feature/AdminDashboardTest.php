<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/cms/dashboard')->assertRedirect('/cms/login');
    }

    public function test_authenticated_user_sees_dashboard(): void
    {
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        $this->actingAs($user)
            ->get('/cms/dashboard')
            ->assertOk()
            ->assertSee('Ekdanta CMS');
    }

    public function test_public_registration_is_closed(): void
    {
        // Staff are created in the CMS — there is no public sign-up.
        $this->get('/register')->assertNotFound();
    }
}
