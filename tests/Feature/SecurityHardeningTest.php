<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Media;
use App\Models\Subscriber;
use App\Models\User;
use App\Support\Csv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regression cover for the audited security fixes: SVG stored-XSS,
 * CSV formula injection, response headers, and server-side RBAC.
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        foreach (config('gsm.roles') as $role) {
            Role::findOrCreate($role, 'web');
        }

        foreach (['media.manage', 'enquiries.manage', 'settings.manage', 'pages.manage', 'users.view'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    /* ── SVG stored XSS ──────────────────────────────────────────── */

    private function maliciousSvg(): string
    {
        return <<<'SVG'
            <?xml version="1.0"?>
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" onload="alert('x')">
              <script type="text/javascript">alert('x')</script>
              <a xlink:href="javascript:alert('x')"><circle cx="5" cy="5" r="4"/></a>
            </svg>
            SVG;
    }

    public function test_uploaded_svg_is_stripped_of_scripts_and_handlers(): void
    {
        $file = UploadedFile::fake()->createWithContent('logo.svg', $this->maliciousSvg());

        $this->actingAs($this->admin())
            ->post('/cms/media', ['files' => [$file]])
            ->assertCreated();

        $stored = Storage::disk('public')->get(Media::firstOrFail()->path);

        $this->assertStringNotContainsString('<script', $stored);
        $this->assertStringNotContainsString('onload', $stored);
        $this->assertStringNotContainsString('javascript:', $stored);
        $this->assertStringContainsString('<circle', $stored, 'legitimate SVG content survives');
    }

    public function test_replacing_a_file_also_sanitises_the_svg(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/cms/media', ['files' => [UploadedFile::fake()->createWithContent('clean.svg', '<svg xmlns="http://www.w3.org/2000/svg"><circle r="1"/></svg>')]])
            ->assertCreated();

        $media = Media::firstOrFail();

        $this->actingAs($admin)->post("/cms/media/{$media->id}/replace", [
            'file' => UploadedFile::fake()->createWithContent('evil.svg', $this->maliciousSvg()),
        ])->assertOk();

        $stored = Storage::disk('public')->get($media->fresh()->path);

        $this->assertStringNotContainsString('<script', $stored);
        $this->assertStringNotContainsString('onload', $stored);
    }

    public function test_executable_uploads_are_still_rejected(): void
    {
        $this->actingAs($this->admin())
            ->postJson('/cms/media', ['files' => [UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;')]])
            ->assertStatus(422);
    }

    /* ── CSV formula injection ───────────────────────────────────── */

    /** @dataProvider formulaPrefixes */
    public function test_csv_cells_starting_with_a_formula_character_are_neutralised(string $raw): void
    {
        $this->assertSame("'".$raw, Csv::cell($raw));
    }

    public static function formulaPrefixes(): array
    {
        return [
            'equals' => ['=1+2'],
            'plus' => ['+1234567890'],
            'minus' => ['-1+1'],
            'at' => ['@SUM(1:9)'],
            'tab' => ["\t=1+1"],
            'carriage return' => ["\r=1+1"],
            'command' => ['=cmd|\' /C calc\'!A0'],
        ];
    }

    public function test_csv_leaves_ordinary_and_numeric_values_alone(): void
    {
        $this->assertSame('Priya Sharma', Csv::cell('Priya Sharma'));
        $this->assertSame('', Csv::cell(''));
        $this->assertNull(Csv::cell(null));
        $this->assertSame(42, Csv::cell(42));
        $this->assertSame(1.5, Csv::cell(1.5));
    }

    public function test_enquiry_export_neutralises_formula_payloads(): void
    {
        Enquiry::create([
            'source' => 'contact',
            'name' => '=1+2',
            'email' => 'formula@example.com',
            'phone' => '+919999999999',
            'message' => '@SUM(1:9)',
            'ip' => '127.0.0.1',
            'status' => 'new',
        ]);

        $csv = $this->actingAs($this->admin())->get('/cms/enquiries/export')->streamedContent();

        $this->assertStringContainsString("'=1+2", $csv);
        $this->assertStringContainsString("'@SUM(1:9)", $csv);
        $this->assertStringNotContainsString(',=1+2', $csv);
    }

    public function test_subscriber_export_neutralises_formula_payloads(): void
    {
        Subscriber::create(['email' => '=cmd@evil.test', 'status' => 'active', 'ip' => '127.0.0.1']);

        $csv = $this->actingAs($this->admin())->get('/cms/subscribers/export')->streamedContent();

        $this->assertStringContainsString("'=cmd@evil.test", $csv);
    }

    /* ── Response headers ────────────────────────────────────────── */

    public function test_security_headers_are_present_on_public_and_admin_responses(): void
    {
        foreach (['/', '/cms/login'] as $url) {
            $response = $this->get($url);

            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
            $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

            $this->assertTrue(
                $response->headers->has('Content-Security-Policy')
                || $response->headers->has('Content-Security-Policy-Report-Only'),
                "No CSP header on {$url}"
            );

            $this->assertFalse($response->headers->has('X-Powered-By'), "X-Powered-By leaked on {$url}");
        }
    }

    public function test_csp_can_be_switched_to_enforcing(): void
    {
        config()->set('security.csp_enforce', true);

        $this->get('/')
            ->assertHeader('Content-Security-Policy')
            ->assertHeaderMissing('Content-Security-Policy-Report-Only');
    }

    /* ── Header injection via the email rule ─────────────────────── */

    /**
     * Laravel 10's loose `email` rule accepts these; submitted addresses end
     * up in mail headers (FormSubmissionMail reply-to), so every validation
     * site uses `email:rfc,strict` instead.
     *
     * @dataProvider crlfAddresses
     */
    public function test_addresses_carrying_a_crlf_are_rejected(string $address): void
    {
        $this->from('/')
            ->post('/newsletter', ['email' => $address])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('subscribers', 0);
    }

    public static function crlfAddresses(): array
    {
        return [
            'quoted local part' => ["\"a\r\nBcc: evil@x.com\"@example.com"],
            'domain literal' => ["a@[127.0.0.1\n]"],
        ];
    }

    public function test_ordinary_addresses_still_pass(): void
    {
        $this->from('/')
            ->post('/newsletter', ['email' => 'reader@example.com'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('subscribers', ['email' => 'reader@example.com']);
    }

    /**
     * A trailing newline never reaches the validator — TrimStrings runs
     * first — so what matters is that nothing with a line break is stored.
     */
    public function test_no_stored_address_can_carry_a_line_break(): void
    {
        $this->from('/')->post('/newsletter', ['email' => "reader2@example.com\n"]);

        foreach (Subscriber::pluck('email') as $stored) {
            $this->assertDoesNotMatchRegularExpression('/[\r\n]/', $stored);
        }
    }

    /* ── Server-side RBAC ────────────────────────────────────────── */

    public function test_permissionless_user_is_forbidden_from_every_gated_screen(): void
    {
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole(config('gsm.roles.viewer'));

        foreach ([
            '/cms/users', '/cms/roles', '/cms/settings/business', '/cms/media',
            '/cms/pages', '/cms/posts', '/cms/enquiries', '/cms/theme-customizer',
            '/cms/global-blocks', '/cms/header-builder', '/cms/footer-builder',
            '/cms/menus', '/cms/redirects', '/cms/activity', '/cms/subscribers',
        ] as $url) {
            $this->actingAs($viewer)->get($url)->assertForbidden();
        }
    }

    public function test_permissionless_user_cannot_write(): void
    {
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->assignRole(config('gsm.roles.viewer'));

        $page = \App\Models\Page::create([
            'title' => 'Probe', 'slug' => 'probe', 'status' => \App\Models\Page::STATUS_DRAFT,
        ]);

        $this->actingAs($viewer)->postJson('/cms/media', ['files' => []])->assertForbidden();
        $this->actingAs($viewer)->postJson('/cms/media-folders', ['name' => 'Nope'])->assertForbidden();
        $this->actingAs($viewer)->postJson('/cms/users', ['name' => 'x'])->assertForbidden();
        $this->actingAs($viewer)->postJson('/cms/roles', ['name' => 'x'])->assertForbidden();
        $this->actingAs($viewer)
            ->postJson('/cms/sections', ['area' => 'page', 'type' => 'hero', 'page_id' => $page->id])
            ->assertForbidden();
    }

    /* ── Section CSS/JS is Developer Mode territory ──────────────── */

    private function pageWithSection(): \App\Models\Section
    {
        $page = \App\Models\Page::create([
            'title' => 'Gate', 'slug' => 'gate', 'status' => \App\Models\Page::STATUS_PUBLISHED,
        ]);

        return $page->sections()->create([
            'type' => 'hero',
            'sort_order' => 0,
            'style' => ['custom_js' => 'window.existing=1;'],
        ]);
    }

    public function test_editor_without_developer_mode_cannot_set_section_js(): void
    {
        Permission::findOrCreate('developer.mode', 'web');
        $section = $this->pageWithSection();

        $editor = User::factory()->create(['is_active' => true]);
        $editor->assignRole(config('gsm.roles.content_manager'));
        $editor->givePermissionTo('pages.manage');

        $this->actingAs($editor)->putJson("/cms/sections/{$section->id}", [
            'device' => 'desktop',
            'data' => null,
            'style' => ['custom_js' => 'window.injected=1;', 'custom_css' => 'body{display:none}', 'pt' => '20'],
        ])->assertOk();

        $section->refresh();

        // The injection is ignored, the pre-existing value survives untouched,
        // and their legitimate spacing change still lands.
        $this->assertSame('window.existing=1;', $section->styleValue('custom_js'));
        $this->assertNull($section->styleValue('custom_css'));
        $this->assertSame('20', $section->styleValue('pt'));
    }

    public function test_developer_mode_holder_can_still_set_section_js(): void
    {
        $section = $this->pageWithSection();

        $this->actingAs($this->admin())->putJson("/cms/sections/{$section->id}", [
            'device' => 'desktop',
            'data' => null,
            'style' => ['custom_js' => 'window.allowed=1;'],
        ])->assertOk();

        $this->assertSame('window.allowed=1;', $section->refresh()->styleValue('custom_js'));
    }

    public function test_role_only_reaches_its_own_modules(): void
    {
        $marketing = User::factory()->create(['is_active' => true]);
        $marketing->assignRole(config('gsm.roles.marketing'));
        $marketing->givePermissionTo('enquiries.manage');

        $this->actingAs($marketing)->get('/cms/enquiries')->assertOk();
        $this->actingAs($marketing)->get('/cms/users')->assertForbidden();
        $this->actingAs($marketing)->get('/cms/settings/business')->assertForbidden();
    }
}
