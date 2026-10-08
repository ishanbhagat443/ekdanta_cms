<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ThemeFont;
use App\Models\ThemePreset;
use App\Models\User;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ThemeCustomizerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        Permission::findOrCreate('theme.manage', 'web');

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    /* ── Access control ───────────────────────────────────────────── */

    public function test_theme_customizer_requires_permission(): void
    {
        Permission::findOrCreate('theme.manage', 'web');
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/cms/theme-customizer')->assertForbidden();
    }

    public function test_index_page_renders_for_authorized_admin(): void
    {
        $this->actingAs($this->admin())->get('/cms/theme-customizer')
            ->assertOk()
            ->assertSee('Theme Customizer')
            ->assertSee('Section Heading')
            ->assertSee('Main Heading (H1)');
    }

    /* ── Defaults resolve from config when nothing stored ────────────── */

    public function test_defaults_resolve_from_config_before_any_save(): void
    {
        $theme = app(ThemeService::class);

        $this->assertSame('#1b2a4a', $theme->get('colors.primary'));
        $this->assertSame(8, $theme->get('radius.buttons'));
        $this->assertSame('Poppins', $theme->get('typography.body_text_family'));
    }

    /* ── Colors group ─────────────────────────────────────────────── */

    public function test_update_colors_persists_and_flushes_cache(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/theme-customizer/colors', [
            'primary' => '#ff0000',
            'secondary' => '#00ff00',
        ])->assertRedirect();

        $theme = app(ThemeService::class);
        $this->assertSame('#ff0000', $theme->get('colors.primary'));
        $this->assertStringContainsString('--gsm-primary:#ff0000;', $theme->renderCss());
    }

    public function test_invalid_color_format_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/theme-customizer/colors', [
            'primary' => 'not-a-color',
        ])->assertSessionHasErrors('primary');
    }

    /* ── Buttons group ────────────────────────────────────────────── */

    public function test_update_buttons_reflected_in_css(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/theme-customizer/buttons', [
            'border_radius' => 20,
            'padding_x' => 30,
            'padding_y' => 14,
            'shadow' => 'lg',
            'hover_animation' => 'lift',
            'gradient_from' => '#111111',
            'gradient_to' => '#222222',
            'icon_position' => 'left',
            'primary_style' => 'gradient',
            'secondary_style' => 'outline',
        ])->assertRedirect();

        $css = app(ThemeService::class)->renderCss();
        $this->assertStringContainsString('border-radius:20px', $css);
        $this->assertStringContainsString('padding:14px 30px', $css);
        $this->assertStringContainsString('linear-gradient(90deg,#111111,#222222)', $css);
        $this->assertStringContainsString('translateY(-2px)', $css); // lift hover
    }

    /* ── Spacing / Radius / Shadows ───────────────────────────────── */

    public function test_spacing_and_radius_and_shadows_generate_css_variables(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/theme-customizer/spacing', ['section_padding_desktop' => 100])->assertRedirect();
        $this->actingAs($admin)->put('/cms/theme-customizer/radius', ['cards' => 18])->assertRedirect();
        $this->actingAs($admin)->put('/cms/theme-customizer/shadows', ['cards' => 'lg'])->assertRedirect();

        $css = app(ThemeService::class)->renderCss();
        $this->assertStringContainsString('--gsm-space-section-padding-desktop:100px;', $css);
        $this->assertStringContainsString('--gsm-radius-cards:18px;', $css);
        $this->assertStringContainsString('.gsm-shadow-cards{box-shadow:0 10px 30px rgba(0,0,0,.14);}', $css);
    }

    /* ── Animations ───────────────────────────────────────────────── */

    public function test_animations_disabled_forces_visible_state(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/theme-customizer/animations', [
            'enabled' => 0, 'default_type' => 'fade', 'duration' => 600, 'delay' => 0,
        ])->assertRedirect();

        $css = app(ThemeService::class)->renderCss();
        $this->assertStringContainsString('.gsm-animate{opacity:1 !important', $css);
    }

    public function test_animation_duration_and_delay_applied(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/theme-customizer/animations', [
            'enabled' => 1, 'default_type' => 'slide', 'duration' => 900, 'delay' => 150,
        ])->assertRedirect();

        $css = app(ThemeService::class)->renderCss();
        $this->assertStringContainsString('transition-duration:900ms;transition-delay:150ms;', $css);
    }

    /* ── Custom CSS/JS per breakpoint ─────────────────────────────── */

    public function test_custom_css_and_js_saved_and_rendered(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/theme-customizer/custom_code', [
            'css_global' => '.foo{color:red;}',
            'css_desktop' => '.bar{color:blue;}',
            'css_tablet' => '.baz{color:green;}',
            'css_mobile' => '.qux{color:orange;}',
            'js_header' => 'console.log("header");',
            'js_footer' => 'console.log("footer");',
            'js_global' => 'console.log("global");',
        ])->assertRedirect();

        $css = app(ThemeService::class)->renderCss();
        $this->assertStringContainsString('.foo{color:red;}', $css);
        $this->assertStringContainsString('@media (min-width: 992px){.bar{color:blue;}}', $css);
        $this->assertStringContainsString('@media (min-width: 768px) and (max-width: 991.98px){.baz{color:green;}}', $css);
        $this->assertStringContainsString('@media (max-width: 767.98px){.qux{color:orange;}}', $css);

        // Rendered on the live front-end.
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('console.log("header");', $html);
        $this->assertStringContainsString('console.log("footer");', $html);
        $this->assertStringContainsString('console.log("global");', $html);
    }

    /* ── Dark mode ────────────────────────────────────────────────── */

    public function test_dark_mode_css_only_emitted_when_enabled(): void
    {
        $admin = $this->admin();
        $theme = app(ThemeService::class);

        $this->assertStringNotContainsString('data-gsm-theme="dark"', $theme->renderCss());

        $this->actingAs($admin)->put('/cms/theme-customizer/dark_mode', [
            'enabled' => 1, 'background' => '#000000',
        ])->assertRedirect();

        $this->assertStringContainsString('html[data-gsm-theme="dark"]{--gsm-background:#000000', app(ThemeService::class)->renderCss());
    }

    /* ── Typography ───────────────────────────────────────────────── */

    public function test_update_typography_for_one_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/theme-customizer/typography', [
            'roles' => [
                'main_heading' => [
                    'family' => 'Montserrat', 'weight' => '700', 'transform' => 'uppercase',
                    'letter_spacing' => '1.5', 'line_height' => '1.2',
                    'size_desktop' => '48', 'size_tablet' => '36', 'size_mobile' => '28',
                ],
            ],
        ])->assertRedirect();

        $theme = app(ThemeService::class);
        $this->assertSame('Montserrat', $theme->get('typography.main_heading_family'));
        $this->assertSame('700', $theme->get('typography.main_heading_weight'));

        $css = $theme->renderCss();
        $this->assertStringContainsString("--gsm-font-main-heading:'Montserrat';", $css);
        $this->assertStringContainsString('--gsm-weight-main-heading:700;', $css);
        $this->assertStringContainsString('--gsm-size-main-heading:48px;', $css);
        $this->assertStringContainsString('.gsm-text-main-heading{', $css);
        $this->assertStringContainsString('text-transform:uppercase;', $css);
    }

    public function test_typography_defaults_untouched_roles_still_resolve(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/theme-customizer/typography', [
            'roles' => ['main_heading' => ['family' => 'Montserrat']],
        ])->assertRedirect();

        // section_heading was never touched — still Lato per the default schema.
        $this->assertSame('Lato', app(ThemeService::class)->get('typography.section_heading_family'));
    }

    public function test_google_fonts_url_built_from_families_in_use(): void
    {
        $url = app(ThemeService::class)->googleFontsUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('family=Poppins', $url);
        $this->assertStringContainsString('family=Lato', $url);
        $this->assertStringContainsString('display=swap', $url);
    }

    public function test_google_fonts_link_rendered_on_frontend(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('fonts.googleapis.com/css2', $html);
    }

    /* ── Local font upload ────────────────────────────────────────── */

    public function test_upload_local_font_and_reference_it_in_typography(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/theme-customizer/fonts', [
            'name' => 'Runiga', 'font_weight' => '700', 'font_style' => 'normal',
            'woff2' => UploadedFile::fake()->create('runiga.woff2', 50),
        ])->assertRedirect();

        $font = ThemeFont::firstOrFail();
        $this->assertSame('runiga', $font->slug);
        Storage::disk('public')->assertExists($font->woff2_path);

        $this->actingAs($admin)->put('/cms/theme-customizer/typography', [
            'roles' => ['main_heading' => ['family' => 'local:'.$font->slug]],
        ])->assertRedirect();

        $css = app(ThemeService::class)->renderCss();
        $this->assertStringContainsString("--gsm-font-main-heading:'Runiga';", $css);
        $this->assertStringContainsString("@font-face { font-family: 'Runiga';", $css);
    }

    public function test_font_upload_requires_at_least_one_file(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/theme-customizer/fonts', ['name' => 'Empty Font'])
            ->assertSessionHasErrors('woff2');
    }

    public function test_delete_local_font_removes_files(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/theme-customizer/fonts', [
            'name' => 'TempFont', 'woff2' => UploadedFile::fake()->create('temp.woff2', 20),
        ]);
        $font = ThemeFont::firstOrFail();
        $path = $font->woff2_path;

        $this->actingAs($admin)->delete("/cms/theme-customizer/fonts/{$font->id}")->assertRedirect();

        $this->assertSame(0, ThemeFont::count());
        Storage::disk('public')->assertMissing($path);
    }

    /* ── Export / Import / Reset ──────────────────────────────────── */

    public function test_export_download_contains_current_settings(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put('/cms/theme-customizer/colors', ['primary' => '#123456']);

        $response = $this->actingAs($admin)->get('/cms/theme-customizer/export/download')->assertOk();
        $payload = json_decode($response->streamedContent(), true);

        $this->assertSame('#123456', $payload['settings']['colors.primary']['value']);
    }

    public function test_import_replaces_all_settings(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put('/cms/theme-customizer/colors', ['primary' => '#111111']);

        $file = UploadedFile::fake()->createWithContent('theme.json', json_encode([
            'settings' => ['colors.primary' => ['value' => '#abcdef', 'type' => 'text']],
        ]));

        $this->actingAs($admin)->post('/cms/theme-customizer/import', ['file' => $file])->assertRedirect();

        $this->assertSame('#abcdef', app(ThemeService::class)->get('colors.primary'));
    }

    public function test_import_rejects_malformed_file(): void
    {
        $admin = $this->admin();
        $file = UploadedFile::fake()->createWithContent('bad.json', json_encode(['nope' => true]));

        $this->actingAs($admin)->post('/cms/theme-customizer/import', ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_reset_clears_all_customizations(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put('/cms/theme-customizer/colors', ['primary' => '#111111']);
        $this->assertSame('#111111', app(ThemeService::class)->get('colors.primary'));

        $this->actingAs($admin)->post('/cms/theme-customizer/reset')->assertRedirect();

        $this->assertSame('#1b2a4a', app(ThemeService::class)->get('colors.primary')); // config default
    }

    /* ── Backup / Restore ─────────────────────────────────────────── */

    public function test_backup_and_restore_round_trip(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put('/cms/theme-customizer/colors', ['primary' => '#111111']);

        $this->actingAs($admin)->post('/cms/theme-customizer/backup', ['name' => 'Before Sale'])->assertRedirect();
        $preset = ThemePreset::where('name', 'Before Sale')->firstOrFail();
        $this->assertSame('#111111', $preset->payload['colors.primary']['value']);

        $this->actingAs($admin)->put('/cms/theme-customizer/colors', ['primary' => '#222222']);
        $this->assertSame('#222222', app(ThemeService::class)->get('colors.primary'));

        $this->actingAs($admin)->post("/cms/theme-customizer/backup/{$preset->id}/restore")->assertRedirect();
        $this->assertSame('#111111', app(ThemeService::class)->get('colors.primary'));
    }

    public function test_system_backup_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $preset = ThemePreset::create(['name' => 'Factory Default', 'slug' => 'factory-default', 'payload' => [], 'is_system' => true]);

        $this->actingAs($admin)->delete("/cms/theme-customizer/backup/{$preset->id}")->assertForbidden();
        $this->assertSame(1, ThemePreset::count());
    }

    /* ── Cache busting ────────────────────────────────────────────── */

    public function test_css_cache_invalidated_after_save(): void
    {
        $admin = $this->admin();
        $firstCss = app(ThemeService::class)->renderCss();
        $this->assertStringContainsString('--gsm-primary:#1b2a4a;', $firstCss);

        $this->actingAs($admin)->put('/cms/theme-customizer/colors', ['primary' => '#654321']);

        $secondCss = app(ThemeService::class)->renderCss();
        $this->assertStringContainsString('--gsm-primary:#654321;', $secondCss);
    }
}
