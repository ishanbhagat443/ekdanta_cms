<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriberAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        Permission::findOrCreate('enquiries.manage', 'web');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    public function test_subscribers_requires_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/subscribers')->assertForbidden();
    }

    public function test_newsletter_signup_appears_in_admin_list(): void
    {
        $this->post('/newsletter', ['email' => 'jane@example.com'])->assertRedirect();

        $admin = $this->admin();
        $this->actingAs($admin)->get('/cms/subscribers')
            ->assertOk()
            ->assertSee('jane@example.com');
    }

    public function test_search_filters_by_email(): void
    {
        $admin = $this->admin();
        Subscriber::create(['email' => 'match@example.com', 'status' => 'active']);
        Subscriber::create(['email' => 'other@example.com', 'status' => 'active']);

        $response = $this->actingAs($admin)->get('/cms/subscribers?search=match')->assertOk();
        $response->assertSee('match@example.com')->assertDontSee('other@example.com');
    }

    public function test_delete_subscriber(): void
    {
        $admin = $this->admin();
        $subscriber = Subscriber::create(['email' => 'gone@example.com', 'status' => 'active']);

        $this->actingAs($admin)->delete("/cms/subscribers/{$subscriber->id}")->assertRedirect();
        $this->assertSame(0, Subscriber::count());
    }

    public function test_export_contains_subscriber_rows_with_bom(): void
    {
        $admin = $this->admin();
        Subscriber::create(['email' => 'export@example.com', 'status' => 'active', 'ip' => '127.0.0.1']);

        $csv = $this->actingAs($admin)->get('/cms/subscribers/export')->assertOk()->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('export@example.com', $csv);
    }
}
