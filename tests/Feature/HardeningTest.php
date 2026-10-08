<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
    }

    public function test_security_headers_present_on_responses(): void
    {
        $response = $this->get('/cms/login');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotEmpty($response->headers->get('Permissions-Policy'));
        // Plain HTTP in tests → no HSTS.
        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    public function test_schema_json_cannot_break_out_of_script_tag(): void
    {
        $page = Page::create([
            'title' => 'Evil', 'slug' => 'evil-page',
            'status' => Page::STATUS_PUBLISHED, 'published_at' => now(),
        ]);
        $page->seoMeta()->create([
            'schema_json' => '{"@type":"Thing","name":"</script><script>alert(1)</script>"}',
        ]);

        $html = $this->get('/evil-page')->assertOk()->getContent();

        $this->assertStringNotContainsString('</script><script>alert(1)</script>', $html);
        $this->assertStringContainsString('</script>', $html); // HEX_TAG-escaped
    }

    public function test_login_throttled_after_five_failures(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'password' => Hash::make('right-password'),
        ]);

        foreach (range(1, 5) as $i) {
            $this->post('/cms/login', ['email' => $user->email, 'password' => 'wrong'])
                ->assertSessionHasErrors('email');
        }

        // 6th attempt hits the lockout ("Too many login attempts" message).
        $response = $this->post('/cms/login', ['email' => $user->email, 'password' => 'wrong']);
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Too many login attempts',
            collect(session('errors')->get('email'))->first(),
        );
    }

    public function test_failed_login_writes_to_security_channel(): void
    {
        Log::shouldReceive('channel')->with('security')->andReturnSelf();
        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('warning')->zeroOrMoreTimes();

        $this->post('/cms/login', ['email' => 'ghost@example.com', 'password' => 'nope']);
    }

    public function test_frontend_images_are_lazy_loaded(): void
    {
        \App\Models\Post::create([
            'title' => 'Lazy Post', 'slug' => 'lazy-post',
            'status' => \App\Models\Post::STATUS_PUBLISHED,
            'published_at' => now()->subHour(), 'body' => 'Body',
            'featured_media_id' => \App\Models\Media::create([
                'disk' => 'public', 'path' => 'media/l.jpg', 'filename' => 'l.jpg',
                'mime' => 'image/jpeg', 'type' => 'image', 'size' => 10,
            ])->id,
        ]);

        $this->get('/blogs')->assertOk()->assertSee('loading="lazy"', false);
    }

    public function test_csrf_required_on_public_form_post(): void
    {
        // CSRF middleware is skipped in the testing environment by design, so
        // assert the config instead: the ONLY exclusion is the Meta WhatsApp
        // webhook (a server-to-server callback authenticated by its own verify
        // token, so a CSRF token can never be supplied).
        $middleware = new \App\Http\Middleware\VerifyCsrfToken(app(), app('encrypter'));
        $except = (new \ReflectionProperty($middleware, 'except'))->getValue($middleware);

        $this->assertSame(['whatsapp/webhook'], $except);
    }

    public function test_backup_command_skips_gracefully_on_sqlite(): void
    {
        // Tests run on sqlite :memory: — command must no-op, not crash.
        $this->artisan('gsm:backup')
            ->expectsOutputToContain('Backup skipped')
            ->assertSuccessful();
    }
}
