<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use App\Services\SettingsService;
use App\Services\ThemeService;
use App\Support\PageRoles;
use Database\Seeders\FooterSeeder;
use Database\Seeders\HeaderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Phase 2: business numbers and offices, the contact file, the title
 * pattern, page roles (home, 404, thank-you), per-page header/footer/code,
 * the Ekdanta theme preset and the Brand design colours.
 */
class SiteAndPageSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        foreach (['pages.manage', 'settings.manage', 'theme.manage', 'developer.mode'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function editor(string ...$permissions): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function setSettings(array $values): void
    {
        $this->actingAs($this->superAdmin())
            ->put('/cms/settings/business', $values)
            ->assertSessionHasNoErrors();

        app(SettingsService::class)->flushCache();
    }

    private function page(string $slug, string $heading, array $attributes = []): Page
    {
        $page = Page::create(array_merge(['title' => ucfirst($slug), 'slug' => $slug, 'status' => Page::STATUS_PUBLISHED], $attributes));
        $page->sections()->create([
            'type' => 'text-block', 'name' => 'Text', 'sort_order' => 0, 'is_active' => true,
            'data_desktop' => ['heading' => $heading],
        ]);

        return $page;
    }

    private function pageUpdate(Page $page, array $extra): array
    {
        return array_merge(['title' => $page->title, 'slug' => $page->slug, 'status' => $page->status], $extra);
    }

    /* ── Business numbers, offices, contact file ─────────────────────── */

    public function test_phone_list_saves_and_builds_call_and_whatsapp_links(): void
    {
        $this->setSettings([
            'company_name' => 'Ekdanta',
            'country_code' => '91',
            'phones' => [
                ['label' => 'All services', 'number' => '90994 17987', 'menu_label' => 'Call', 'whatsapp' => '1'],
                ['label' => '', 'number' => '', 'menu_label' => ''], // blank row from the form is dropped
                ['label' => 'London', 'number' => '+44 20 7946 0000', 'menu_label' => ''],
            ],
        ]);

        $phones = business_phones();

        $this->assertCount(2, $phones);
        $this->assertSame('tel:+919099417987', $phones[0]['tel']);
        $this->assertSame('https://wa.me/919099417987', $phones[0]['wa']);
        $this->assertTrue($phones[0]['whatsapp']);
        $this->assertSame('tel:+442079460000', $phones[1]['tel']);
        $this->assertSame('https://wa.me/919099417987', business_whatsapp()['wa'] ?? null);
    }

    public function test_a_phone_number_with_letters_is_rejected(): void
    {
        $this->actingAs($this->superAdmin())
            ->from('/cms/settings/business')
            ->put('/cms/settings/business', ['phones' => [['number' => 'call me <b>']]])
            ->assertSessionHasErrors('phones.0.number');
    }

    public function test_offices_list_saves(): void
    {
        $this->setSettings(['offices' => [
            ['name' => 'Surat', 'address' => '[Add office address]', 'directions_url' => 'https://maps.google.com/?q=Surat', 'latitude' => '21.17', 'longitude' => '72.83', 'hours' => '[Add office hours]'],
        ]]);

        $this->assertSame('[Add office hours]', business_offices()[0]['hours']);

        $this->get('/cms/settings/business')->assertOk()
            ->assertSee('name="offices[0][hours]"', false)
            ->assertSee('value="[Add office hours]"', false)
            ->assertSee('data-name="offices[__i__][hours]"', false);
    }

    public function test_contact_file_lists_every_number(): void
    {
        $this->setSettings([
            'company_name' => 'Ekdanta',
            'country_code' => '91',
            'phones' => [
                ['label' => 'All services', 'number' => '90994 17987', 'whatsapp' => '1'],
                ['label' => 'Sales', 'number' => '92742 03144'],
            ],
        ]);

        $response = $this->get('/contact.vcf')->assertOk();

        $this->assertStringContainsString('text/vcard', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('ekdanta.vcf', (string) $response->headers->get('Content-Disposition'));
        $vcard = $response->getContent();
        $this->assertStringContainsString('FN:Ekdanta', $vcard);
        $this->assertStringContainsString(':+919099417987', $vcard);
        $this->assertStringContainsString(':+919274203144', $vcard);
        $this->assertStringContainsString('X-ABLabel:Sales', $vcard);
    }

    public function test_title_pattern_names_each_page(): void
    {
        $this->actingAs($this->superAdmin())
            ->put('/cms/settings/seo', ['meta_title' => '{page} · Ekdanta'])
            ->assertSessionHasNoErrors();
        app(SettingsService::class)->flushCache();

        $this->page('services', 'Services heading');

        $this->get('/services')->assertOk()->assertSee('<title>Services · Ekdanta</title>', false);
    }

    /* ── Page roles ──────────────────────────────────────────────────── */

    public function test_the_home_role_serves_its_page_at_the_root_and_redirects_its_slug(): void
    {
        $page = $this->page('start', 'Ekdanta home heading');

        $this->actingAs($this->superAdmin())
            ->put(route('admin.pages.update', $page), $this->pageUpdate($page, ['page_role' => 'home']))
            ->assertSessionHasNoErrors();

        $this->get('/')->assertOk()->assertSee('Ekdanta home heading');
        $this->get('/start')->assertRedirect('/');
        $this->assertSame(url('/').'?preview=1', $page->fresh()->previewUrl());
    }

    public function test_a_role_moves_when_another_page_takes_it(): void
    {
        $first = $this->page('first', 'First');
        $second = $this->page('second', 'Second');

        PageRoles::assign($first, 'thank_you');
        PageRoles::assign($second, 'thank_you');

        $this->assertNull(PageRoles::roleOf($first));
        $this->assertSame('thank_you', PageRoles::roleOf($second));
    }

    public function test_the_404_role_page_renders_with_a_404_status(): void
    {
        $page = $this->page('not-found', 'Lost? Here is the way back');
        PageRoles::assign($page, 'not_found');

        $this->get('/no-such-address')->assertNotFound()->assertSee('Lost? Here is the way back');
        $this->assertDatabaseHas('not_found_logs', ['path' => '/no-such-address']);
    }

    public function test_the_404_role_page_only_counts_once_published(): void
    {
        $page = $this->page('not-found', 'Draft 404 heading', ['status' => Page::STATUS_DRAFT]);
        PageRoles::assign($page, 'not_found');

        $this->get('/no-such-address')->assertNotFound()->assertDontSee('Draft 404 heading');
    }

    public function test_sitemap_skips_404_thank_you_and_template_pages(): void
    {
        $this->page('about', 'About');
        PageRoles::assign($this->page('not-found', '404'), 'not_found');
        PageRoles::assign($this->page('thank-you', 'Thanks'), 'thank_you');
        $this->page('case-template', 'Template', ['settings' => ['template_for' => 'case_study']]);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString(url('/about'), $xml);
        $this->assertStringNotContainsString(url('/not-found'), $xml);
        $this->assertStringNotContainsString(url('/thank-you'), $xml);
        $this->assertStringNotContainsString(url('/case-template'), $xml);
    }

    /* ── Per-page header, footer and code ────────────────────────────── */

    public function test_page_header_style_and_footer_switch(): void
    {
        $this->seed([HeaderSeeder::class, FooterSeeder::class]);
        $page = $this->page('contact', 'Contact heading');

        $this->get('/contact')->assertSee('gsm-site-header', false)->assertSee('gsm-footer', false);

        $this->actingAs($this->superAdmin())
            ->put(route('admin.pages.update', $page), $this->pageUpdate($page, [
                'header' => ['variant' => 'pill', 'cta_label' => 'Get a quote', 'cta_url' => '/contact#c-brief'],
                'show_footer' => '0',
            ]))
            ->assertSessionHasNoErrors();

        $html = $this->get('/contact')->assertOk()->getContent();
        $this->assertStringContainsString('gsm-header-pill', $html);
        $this->assertStringNotContainsString('class="gsm-footer', $html);

        $this->put(route('admin.pages.update', $page), $this->pageUpdate($page, ['header' => ['variant' => 'hidden']]));
        $this->get('/contact')->assertDontSee('gsm-site-header', false);
    }

    public function test_page_code_needs_developer_mode(): void
    {
        $page = $this->page('contact', 'Contact heading');
        $code = ['code' => ['js' => 'window.pageCodeRan = true;', 'css' => '.x{color:red}']];

        $this->actingAs($this->editor('pages.manage'))
            ->put(route('admin.pages.update', $page), $this->pageUpdate($page, $code))
            ->assertSessionHasNoErrors();
        $this->assertSame([], (array) $page->fresh()->setting('code', []));

        $this->actingAs($this->superAdmin())
            ->put(route('admin.pages.update', $page), $this->pageUpdate($page, $code));
        $this->get('/contact')->assertSee('window.pageCodeRan = true;', false);
    }

    public function test_builder_preview_offers_exact_sizes_and_reduce_motion(): void
    {
        $page = $this->page('contact', 'Contact heading');

        $this->actingAs($this->superAdmin())->get(route('admin.pages.edit', $page))
            ->assertOk()
            ->assertSee('data-gsm-preview-size="1440x900"', false)
            ->assertSee('data-gsm-preview-size="390x844"', false)
            ->assertSee('id="gsm-preview-reduce"', false);

        $this->get('/contact?reduce-motion=1')->assertSee('data-motion="off"', false);
    }

    /* ── Theme preset and Brand design colours ───────────────────────── */

    public function test_ekdanta_preset_imports_through_theme_customizer(): void
    {
        $file = UploadedFile::fake()->createWithContent('ekdanta-theme.json', (string) file_get_contents(database_path('presets/ekdanta-theme.json')));

        $this->actingAs($this->superAdmin())
            ->post(route('admin.theme.import'), ['file' => $file])
            ->assertSessionHasNoErrors();

        $theme = app(ThemeService::class);
        $this->assertSame('#2EB45D', $theme->get('colors.primary'));
        $this->assertSame('Hind', $theme->get('typography.body_text_family'));
        $this->assertStringContainsString('--tk-finale: #2CAA58;', (string) $theme->get('custom_code.css_variables'));

        $fonts = implode(' ', $theme->googleFontsUrls());
        $this->assertStringContainsString('family=Poppins:wght@500;600;700;800', $fonts);
        $this->assertStringContainsString('family=Hind:wght@400;500;600', $fonts);
    }

    public function test_theme_import_skips_malformed_rows(): void
    {
        $file = UploadedFile::fake()->createWithContent('theme.json', json_encode(['settings' => [
            'colors.primary' => ['value' => '#123456', 'type' => 'text'],
            'no-dot' => ['value' => 'x'],
            'colors.secondary' => ['value' => ['nested']],
        ]]));

        $this->actingAs($this->superAdmin())
            ->post(route('admin.theme.import'), ['file' => $file])
            ->assertSessionHasNoErrors();

        $theme = app(ThemeService::class);
        $this->assertSame('#123456', $theme->get('colors.primary'));
        $this->assertSame(config('theme.colors.fields.secondary.default'), $theme->get('colors.secondary'));
    }

    public function test_brand_edits_design_colours_in_place(): void
    {
        $theme = app(ThemeService::class);
        $theme->updateGroup('custom_code', ['css_variables' => "--tk-green: #2EB45D;\n--tk-shadow-sm: 0 8px 20px rgba(0,0,0,.4);\n--tk-mint-a: #8FE3AE80;"]);

        $this->actingAs($this->superAdmin())->get(route('admin.brand.index'))
            ->assertOk()
            ->assertSee('name="tokens[--tk-green]"', false)
            ->assertDontSee('tokens[--tk-mint-a]', false); // alpha stays in Theme Customizer

        $this->put(route('admin.brand.tokens'), ['tokens' => ['--tk-green' => '#2CAA58']])
            ->assertRedirect(route('admin.brand.index'));

        $this->assertSame(
            "--tk-green: #2CAA58;\n--tk-shadow-sm: 0 8px 20px rgba(0,0,0,.4);\n--tk-mint-a: #8FE3AE80;",
            $theme->get('custom_code.css_variables'),
        );

        $this->put(route('admin.brand.tokens'), ['tokens' => ['--tk-green' => 'red;}body{']])
            ->assertSessionHasErrors('tokens.--tk-green');
    }
}
