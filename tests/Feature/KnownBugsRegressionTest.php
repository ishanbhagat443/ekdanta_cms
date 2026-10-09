<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ButtonStyle;
use App\Models\Enquiry;
use App\Models\Form;
use App\Models\GlobalBlock;
use App\Models\Gradient;
use App\Models\Layout;
use App\Models\Page;
use App\Models\User;
use App\Services\LayoutService;
use App\Services\SectionTypes\SectionTypeRegistry;
use App\Services\ThemeService;
use Database\Seeders\HeaderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * One test (or a few) per bug from the 8 Oct 2026 audit, named after it, so
 * none of them can quietly come back. Bug 3 (Springy) is covered in
 * tests/js/motion.test.mjs; bug 4 in ThemeCustomizerTest.
 */
class KnownBugsRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        foreach (['pages.manage', 'builder.header', 'builder.footer', 'theme.manage', 'blocks.manage', 'forms.manage', 'developer.mode'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    /** An editor holding the given permissions, but not Developer Mode. */
    private function editor(string ...$permissions): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    /* ── 1. Home page preview opened /home and 404'd ──────────────── */

    public function test_bug_01_the_home_page_previews_at_the_root(): void
    {
        $home = Page::create(['title' => 'Home', 'slug' => 'home', 'status' => Page::STATUS_DRAFT]);
        $home->sections()->create([
            'type' => 'text-block', 'name' => 'Text', 'sort_order' => 0, 'is_active' => true,
            'data_desktop' => ['heading' => 'Draft home heading'],
        ]);

        $this->assertSame(url('/').'?preview=1', $home->previewUrl());
        $this->assertSame(url('/about').'?preview=1', (new Page(['slug' => 'about']))->previewUrl());

        $this->actingAs($this->superAdmin())->get('/?preview=1')->assertOk()->assertSee('Draft home heading');

        // Visitors never see the draft.
        auth()->logout();
        $this->get('/?preview=1')->assertOk()->assertDontSee('Draft home heading');
    }

    public function test_bug_01_home_redirects_to_the_root_keeping_its_query(): void
    {
        $this->get('/home')->assertStatus(301)->assertRedirect('/');
        $this->get('/home?utm_source=ad')->assertStatus(301)->assertRedirect('/?utm_source=ad');
    }

    /* ── 2. The 404 page printed raw Blade ────────────────────────── */

    public function test_bug_02_error_pages_render_their_message(): void
    {
        $this->get('/no-such-page-anywhere')
            ->assertNotFound()
            ->assertSee('exist, or it has moved', false)
            ->assertDontSee('@section', false)
            ->assertDontSee('@endsection', false);

        $html = view('errors.403')->render();
        $this->assertStringContainsString('permission to view this page', $html);
        $this->assertStringNotContainsString('@section', $html);
    }

    /* ── 5. A font stack was quoted as one family name ────────────── */

    public function test_bug_05_font_stacks_quote_each_family(): void
    {
        $this->assertSame("'Georgia', 'Times New Roman', serif", ThemeService::fontStack('Georgia, "Times New Roman", serif'));
        $this->assertSame("'Poppins'", ThemeService::fontStack('Poppins'));
        // Nothing that can leave the declaration survives: ; } < > are dropped.
        $this->assertSame("'Badstyle'", ThemeService::fontStack('Bad;}</style>'));

        app(ThemeService::class)->updateTypography(['main_heading' => ['family' => 'Georgia, "Times New Roman", serif']]);
        $this->assertStringContainsString("--gsm-font-main-heading:'Georgia', 'Times New Roman', serif;", app(ThemeService::class)->renderCss());
    }

    /* ── 6. Brand and Typography read different keys ──────────────── */

    public function test_bug_06_brand_and_typography_show_the_same_font_and_keep_unlisted_ones(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put('/cms/theme-customizer/typography', [
            'roles' => ['main_heading' => ['family' => 'Fraunces']], // not in the suggestion list
        ])->assertRedirect();

        $this->actingAs($admin)->get('/cms/theme-customizer')->assertOk()->assertSee('value="Fraunces"', false);
        $this->actingAs($admin)->get('/cms/brand')->assertOk()->assertSee('Fraunces');

        $this->actingAs($admin)->put('/cms/theme-customizer/typography', [
            'roles' => ['main_heading' => ['family' => 'Bad; color: red}']],
        ])->assertSessionHasErrors('roles.main_heading.family');
    }

    /* ── 7. <body> ignored the Body Text font ─────────────────────── */

    public function test_bug_07_plain_elements_follow_the_typography_roles(): void
    {
        $css = app(ThemeService::class)->renderCss();

        $this->assertStringContainsString('--bs-body-font-family:var(--gsm-font-body-text)', $css);
        $this->assertStringContainsString('body{font-family:var(--gsm-font-body-text)', $css);
        $this->assertStringContainsString('h1{font-family:var(--gsm-font-main-heading)', $css);
    }

    /* ── 9. No desktop navigation; 10. unreadable active item ─────── */

    public function test_bug_09_navigation_style_is_a_per_device_header_setting(): void
    {
        $this->seed(HeaderSeeder::class);
        $header = Layout::query()->where('area', Layout::AREA_HEADER)->firstOrFail();

        // Default: links in the header on desktop, menu button elsewhere.
        app(LayoutService::class)->flushCache();
        $this->get('/')->assertOk()
            ->assertSee('gsm-site-header__nav d-none d-md-none d-lg-flex', false)
            ->assertSee('gsm-main-menu d-inline-flex d-md-inline-flex d-lg-none', false);

        $this->actingAs($this->superAdmin())->put('/cms/header-builder', [
            'nav_style' => ['desktop' => 'inline', 'tablet' => 'inline', 'mobile' => 'overlay'],
        ])->assertRedirect();
        $this->assertSame('overlay', $header->fresh()->setting('nav_style.mobile'));

        $page = $this->get('/')->assertOk();
        $page->assertSee('gsm-site-header__nav d-none d-md-flex d-lg-flex', false);
        $page->assertSee('<dialog id="gsm-nav-overlay"', false);
        $page->assertSee('data-gsm-menu-open', false);

        $this->actingAs($this->superAdmin())->put('/cms/header-builder', [
            'nav_style' => ['desktop' => 'sideways'],
        ])->assertSessionHasErrors('nav_style.desktop');
    }

    public function test_bug_10_the_current_menu_item_is_marked_for_assistive_tech(): void
    {
        $this->seed(HeaderSeeder::class);
        app(LayoutService::class)->flushCache();

        $this->get('/')->assertOk()->assertSee('aria-current="page"', false);
    }

    /* ── 11 + 12. Previous client's design data ───────────────────── */

    public function test_bug_11_12_seeded_design_data_is_neutralised_but_edits_are_kept(): void
    {
        $oldStops = [
            ['color' => '#14203a', 'opacity' => 100, 'position' => 2],
            ['color' => '#1b2a4a', 'opacity' => 100, 'position' => 19],
            ['color' => '#c29b54', 'opacity' => 100, 'position' => 50],
            ['color' => '#F03225', 'opacity' => 100, 'position' => 100],
        ];
        Gradient::create(['slug' => 'primary-gradient', 'name' => 'Primary Gradient', 'type' => 'linear', 'angle' => 90,
            'description' => 'Brand magenta → orange sweep used on primary CTAs (design reference).', 'stops' => $oldStops, 'is_active' => true]);
        ButtonStyle::create(['slug' => 'primary-pill', 'name' => 'Primary Pill', 'is_active' => true,
            'description' => 'Design CTA (02 Hero.png): multi-stop brand gradient, rounded left edge, uppercase.',
            'config' => array_replace_recursive(ButtonStyle::defaults(), [
                'border' => ['radius' => ['tl' => '999px', 'tr' => '12px', 'br' => '12px', 'bl' => '999px']],
                'shadows' => [['x' => 0, 'y' => 6, 'blur' => 18, 'spread' => 0, 'color' => '#F03225', 'opacity' => 25, 'inset' => false]],
            ])]);
        // An admin already changed this one: it must not be touched.
        ButtonStyle::create(['slug' => 'secondary-solid', 'name' => 'Secondary Solid', 'is_active' => true,
            'description' => 'Our own description', 'config' => ButtonStyle::defaults()]);
        $dealer = Form::create(['name' => 'Dealer Form', 'slug' => 'dealer-form', 'is_active' => true, 'enquiry_source' => 'dealer']);

        $migration = require database_path('migrations/2026_10_08_000002_retire_previous_client_design_data.php');
        $migration->up();

        $pill = ButtonStyle::where('slug', 'primary-pill')->first();
        $this->assertSame(['tl' => '999px', 'tr' => '999px', 'br' => '999px', 'bl' => '999px'], $pill->config['border']['radius']);
        $this->assertSame([], $pill->config['shadows']);
        $this->assertStringNotContainsString('02 Hero', $pill->description);
        $this->assertSame('#111827', Gradient::where('slug', 'primary-gradient')->value('stops')[0]['color']);
        $this->assertSame('Our own description', ButtonStyle::where('slug', 'secondary-solid')->value('description'));
        $this->assertFalse($dealer->fresh()->is_active, 'retired, not deleted');

        $migration->down();
        $this->assertSame($oldStops, Gradient::where('slug', 'primary-gradient')->value('stops'));
        $this->assertSame('12px', ButtonStyle::where('slug', 'primary-pill')->first()->config['border']['radius']['tr']);
    }

    public function test_bug_12_enquiry_sources_are_the_forms_themselves(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->post('/cms/forms', ['name' => 'Project Brief'])->assertRedirect();
        $form = Form::where('name', 'Project Brief')->firstOrFail();
        $this->assertNull($form->enquiry_source);

        // The switch on the form's edit screen.
        $this->actingAs($admin)->put("/cms/forms/{$form->id}", ['name' => 'Project Brief', 'store_as_enquiry' => 1])->assertRedirect();
        $form->refresh();
        $this->assertSame($form->slug, $form->enquiry_source, 'one source per form, keyed by the form');
        $this->assertSame('Project Brief', Enquiry::sources()[$form->slug]);
        $this->assertSame('Project Brief', (new Enquiry(['source' => $form->slug]))->sourceLabel());
    }

    public function test_bug_12_no_previous_client_names_reach_the_public_site(): void
    {
        $this->assertSame('Decorative Separator (Image)', app(SectionTypeRegistry::class)->find('separator')->label());
        $this->assertSame('Image Gallery + Icon Cards', app(SectionTypeRegistry::class)->find('artistry')->label());

        // No company name set: the title must not fall back to the CMS's own name.
        $this->get('/no-such-page')->assertDontSee('Ekdanta CMS');
        $this->get('/')->assertDontSee(config('gsm.name'));
    }

    /* ── 13. Site-wide scripts need Developer Mode ────────────────── */

    public function test_bug_13_header_and_footer_js_need_developer_mode(): void
    {
        $header = Layout::query()->firstOrCreate(['area' => Layout::AREA_HEADER]);
        $header->update(['custom_js' => 'console.log("developer script")']);

        $this->actingAs($this->editor('builder.header'))->put('/cms/header-builder', [
            'custom_js' => 'alert("not allowed")',
        ])->assertRedirect();
        $this->assertSame('console.log("developer script")', $header->fresh()->custom_js, 'kept, not replaced');

        // A developer can change it, and can clear it (this used to be impossible).
        $this->actingAs($this->superAdmin())->put('/cms/header-builder', ['custom_js' => ''])->assertRedirect();
        $this->assertNull($header->fresh()->custom_js);

        $footer = Layout::query()->firstOrCreate(['area' => Layout::AREA_FOOTER]);
        $this->actingAs($this->editor('builder.footer'))->put('/cms/footer-builder', ['custom_js' => 'alert(1)'])->assertRedirect();
        $this->assertNull($footer->fresh()->custom_js);
    }

    public function test_bug_13_theme_scripts_need_developer_mode_including_imports(): void
    {
        $editor = $this->editor('theme.manage');

        $this->actingAs($editor)->put('/cms/theme-customizer/custom_code', [
            'js_header' => 'alert(1)', 'css_global' => '.x{color:red}',
        ])->assertRedirect();
        $this->assertNull(theme('custom_code.js_header'));
        $this->assertSame('.x{color:red}', theme('custom_code.css_global'), 'CSS stays a theme privilege');

        $file = UploadedFile::fake()->createWithContent('theme.json', json_encode(['settings' => [
            'custom_code.js_footer' => ['value' => 'alert(2)', 'type' => 'code'],
        ]]));
        $this->actingAs($editor)->post('/cms/theme-customizer/import', ['file' => $file])->assertRedirect();
        $this->assertNull(app(ThemeService::class)->get('custom_code.js_footer'));
    }

    public function test_bug_13_css_fields_cannot_close_their_style_tag(): void
    {
        $this->seed(HeaderSeeder::class);
        Layout::query()->where('area', Layout::AREA_HEADER)->update(['custom_css' => '.a{}</style><script>alert(1)</script>']);
        app(LayoutService::class)->flushCache();

        $this->get('/')->assertOk()
            ->assertDontSee('</style><script>alert(1)', false)
            ->assertSee('<\/style><script>alert(1)<\/script>', false);
    }

    public function test_bug_13_global_blocks_drop_section_js_without_developer_mode(): void
    {
        $block = GlobalBlock::create(['name' => 'Promo', 'slug' => 'promo', 'type' => 'text-block', 'status' => 'active',
            'version' => 1, 'data_desktop' => [], 'style' => []]);

        $this->actingAs($this->editor('blocks.manage'))->putJson("/cms/global-blocks/{$block->id}", [
            'device' => 'desktop', 'style' => ['custom_js' => 'alert(1)', 'pt' => 10],
        ])->assertOk();

        $style = (array) $block->fresh()->style;
        $this->assertArrayNotHasKey('custom_js', $style);
        $this->assertSame(10, $style['pt']);
    }
}
