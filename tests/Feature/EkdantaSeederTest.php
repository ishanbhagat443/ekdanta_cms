<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GlobalBlock;
use App\Models\Layout;
use App\Models\Menu;
use App\Models\Page;
use App\Models\ThemePreset;
use App\Services\ThemeService;
use App\Support\PageRoles;
use App\Services\SettingsService;
use Database\Seeders\EkdantaSiteSeeder;
use Database\Seeders\HeaderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** `php artisan ekdanta:seed` sets up the site once and never overwrites what the admin changed. */
class EkdantaSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedSite(): void
    {
        $seeder = new EkdantaSiteSeeder;
        $seeder->business();
        $seeder->menus();
        $seeder->header();
    }

    public function test_it_replaces_the_starter_header_and_runs_again_without_duplicates(): void
    {
        $this->seed(HeaderSeeder::class);

        $this->seedSite();
        $this->seedSite();

        $header = Layout::query()->where('area', Layout::AREA_HEADER)->first();
        $this->assertSame(['logo', 'nav-menu', 'cta-button'], $header->sections()->orderBy('sort_order')->pluck('type')->all());
        $this->assertSame('pill', $header->headerVariant());
        $this->assertSame('overlay', $header->navStyle('mobile'));
        $this->assertSame(27, $header->sections()->where('type', 'logo')->first()->dataFor('mobile')['max_height']);
        $this->assertSame(['Home', 'Services', 'Work', 'About', 'Blog', 'Contact'], Menu::query()->where('slug', 'ekdanta-main-menu')->first()->items()->orderBy('sort_order')->pluck('label')->all());
        $this->assertSame(1, Menu::query()->where('slug', 'like', 'ekdanta-%')->count(), 'one menu everywhere');
        $this->assertCount(3, business_phones());

        $this->get('/')->assertSee('gsm-header-pill', false)->assertSee('Become a big brand');
    }

    public function test_it_leaves_a_customised_header_and_filled_settings_alone(): void
    {
        $header = Layout::query()->create(['area' => Layout::AREA_HEADER, 'settings' => ['variant' => 'normal']]);
        $header->sections()->create(['type' => 'cta-button', 'name' => 'Call', 'sort_order' => 0, 'is_active' => true, 'data_desktop' => ['label' => 'Call us', 'url' => '/contact']]);
        app(SettingsService::class)->updateGroup('business', ['email' => 'owner@example.com']);

        $this->seedSite();

        $this->assertSame(['cta-button'], $header->sections()->pluck('type')->all());
        $this->assertSame('normal', $header->fresh()->headerVariant());
        $this->assertSame('owner@example.com', setting('business.email'));
        $this->assertSame('91', setting('business.country_code'));
    }

    public function test_office_hours_still_holding_the_placeholder_get_the_real_ones_and_typed_ones_stay(): void
    {
        $office = ['name' => 'Surat', 'address' => 'Bhatar', 'directions_url' => '', 'latitude' => '', 'longitude' => ''];
        app(SettingsService::class)->updateGroup('business', ['offices' => [$office + ['hours' => '[Add office hours]']]]);
        (new EkdantaSiteSeeder)->business();
        $this->assertSame('Mon–Sat, 10 am – 6:30 pm', business_offices()[0]['hours']);
        $this->assertSame('Bhatar', business_offices()[0]['address'], 'the rest of the office is kept');

        app(SettingsService::class)->updateGroup('business', ['offices' => [$office + ['hours' => 'Mon–Fri, 9–5']]]);
        (new EkdantaSiteSeeder)->business();
        $this->assertSame('Mon–Fri, 9–5', business_offices()[0]['hours']);
    }

    public function test_the_brief_gains_consent_and_required_details_once_and_keeps_edits(): void
    {
        $seeder = new EkdantaSiteSeeder;
        $seeder->briefForm();
        $form = \App\Models\Form::query()->where('slug', 'ekdanta-brief')->sole();
        // As built before the consent work, and with one label the admin changed.
        $form->fields()->where('type', 'consent')->delete();
        $form->fields()->whereIn('name', ['your_name', 'phone_number'])->update(['is_required' => false]);
        $form->fields()->where('name', 'company_or_brand')->update(['label' => 'Your brand']);

        $seeder->briefForm();
        $seeder->briefForm();

        $fields = $form->fields()->get()->keyBy('name');
        $this->assertTrue($fields['your_name']->is_required && $fields['phone_number']->is_required);
        $this->assertFalse($fields['company_or_brand']->is_required);
        $this->assertSame('Your brand', $fields['company_or_brand']->label, 'a label the admin changed stays');
        $consent = $form->fields()->where('type', 'consent')->sole();
        $this->assertTrue($consent->is_required);
        $this->assertSame(4, $consent->step(), 'on the last step');
        $this->assertSame('I agree that Ekdanta can contact me about this brief.', $consent->setting('short_label'));
    }

    public function test_new_fields_reach_sections_built_before_them_and_nothing_set_changes(): void
    {
        $page = Page::create(['title' => 'Blog', 'slug' => 'blog', 'status' => Page::STATUS_PUBLISHED]);
        // As built before the small print had a link; the editor's note, and an empty button label on purpose.
        $section = $page->sections()->create(['type' => 'ekdanta-whatsapp-tips', 'name' => 'Tips', 'sort_order' => 0, 'is_active' => true,
            'data_desktop' => ['note' => 'You join by sending us the message yourself. Leave any time by replying STOP.', 'button_label' => '']]);

        (new EkdantaSiteSeeder)->newFields();
        (new EkdantaSiteSeeder)->newFields();

        $data = $section->fresh()->data_desktop;
        $this->assertSame('You join by sending us the message yourself. Leave any time by replying STOP.', $data['note']);
        $this->assertSame('', $data['button_label'], 'a value that is there, even empty, stays');
        $this->assertSame('Privacy Policy', $data['note_link_label']);
        $this->assertSame('/privacy-policy', $data['note_link']);
        $this->assertSame('Ek tip,', $data['heading']);

        $this->get('/blog')->assertOk()->assertSee('Leave any time by replying STOP. <a href="/privacy-policy" style="color: var(--tk-mint)">Privacy Policy</a>', false);
    }

    public function test_it_fills_the_legal_entity_footer_menu_and_share_image_only_where_empty(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        app(SettingsService::class)->updateGroup('business', ['trade_name' => 'My trade name']);
        $seeder = new EkdantaSiteSeeder;
        foreach (['media', 'business', 'shareImage', 'menus', 'finale', 'business', 'shareImage', 'finale'] as $step) {
            $seeder->{$step}();
        }

        $this->assertSame('My trade name', setting('business.trade_name'), 'a value someone typed stays');
        $this->assertSame('Bhagat Dishant Dipesh', setting('business.legal_name'));
        $this->assertSame('Proprietorship', setting('business.constitution'));
        $this->assertSame('24GHNPD1228K1ZC', setting('business.gst'));
        $this->assertStringStartsWith('Shop No. 403, 4th Floor', setting('business.registered_address'));

        $finale = GlobalBlock::query()->where('slug', 'big-brand-finale')->sole();
        $this->assertSame(Menu::query()->where('slug', 'ekdanta-main-menu')->value('id'), $finale->data_desktop['menu_id']);

        $og = \App\Models\Media::query()->where('key', 'ekdanta/og-default')->sole();
        $this->assertSame('Ekdanta — Aapki branding & marketing ke विघ्नहर्ता', $og->alt);
        $this->assertSame('Vaishali Hirpara, founder of Ekdanta', \App\Models\Media::query()->where('key', 'ekdanta/founder-vaishali-hirpara')->value('alt'));
        $path = setting('seo.og_image');
        $this->assertNotSame($og->path, $path, 'the setting has its own copy');
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($path);
        $seeder->shareImage();
        $this->assertSame($path, setting('seo.og_image'), 'set once');
    }

    public function test_it_applies_the_theme_once_and_keeps_the_old_one_as_a_backup(): void
    {
        $seeder = new EkdantaSiteSeeder;
        $seeder->theme();

        $this->assertStringContainsString('--tk-ink: #0D1410', (string) app(ThemeService::class)->get('custom_code.css_variables'));
        $this->assertTrue(ThemePreset::query()->where('slug', 'ekdanta')->exists());
        $this->assertTrue(ThemePreset::query()->where('name', 'Before Ekdanta')->exists());

        // Edited afterwards: a second run keeps the edit.
        app(ThemeService::class)->updateGroup('colors', ['primary' => '#123456']);
        $seeder->theme();
        $this->assertSame('#123456', app(ThemeService::class)->get('colors.primary'));
        $this->assertSame(1, ThemePreset::query()->where('name', 'Before Ekdanta')->count());
    }

    public function test_with_pages_it_builds_every_page_once_around_one_synced_finale(): void
    {
        $kept = Page::create(['title' => 'Work', 'slug' => 'work', 'status' => Page::STATUS_PUBLISHED]);
        $kept->sections()->create(['type' => 'text-block', 'name' => 'Mine', 'sort_order' => 0, 'is_active' => true, 'data_desktop' => ['heading' => 'Mine']]);

        $seeder = new EkdantaSiteSeeder;
        $seeder->withPages = true;
        foreach (['collections', 'menus', 'finale', 'briefForm', 'pages', 'pages'] as $step) {
            $seeder->{$step}();
        }

        $finale = GlobalBlock::query()->where('slug', 'big-brand-finale')->sole();
        $home = Page::query()->where('slug', 'home')->sole();
        $this->assertSame(['ekdanta-home-hero', 'ekdanta-obstacles', 'ekdanta-work-showcase', 'ekdanta-reels-carousel', 'ekdanta-brand-shelves', 'ekdanta-services-panels', 'ekdanta-founders-story', 'ekdanta-finale'],
            $home->sections()->orderBy('sort_order')->pluck('type')->all());
        $this->assertSame(10, $finale->instances()->where('is_synced', true)->count(), 'every page but the one kept');
        $this->assertSame(['Mine'], $kept->sections()->pluck('name')->all(), 'a page with sections is left alone');
        $this->assertTrue($home->is(PageRoles::page('home')));
        $this->assertSame('page-not-found', PageRoles::page('not_found')->slug);
        $this->assertSame('post', Page::query()->where('slug', 'article-template')->sole()->setting('template_for'));
        $this->assertSame([], Page::query()->whereIn('slug', array_keys(EkdantaSiteSeeder::PAGES))->get()->filter(fn ($p) => $p->setting('header.menu_id'))->pluck('slug')->all(), 'every page uses the header\'s menu');

        $this->get('/')->assertOk()->assertSee('j-top', false);
        $this->get('/contact')->assertOk()->assertSee('c-brief', false);
        $this->get('/no-such-page')->assertNotFound()->assertSee('n-routes', false);
    }
}
