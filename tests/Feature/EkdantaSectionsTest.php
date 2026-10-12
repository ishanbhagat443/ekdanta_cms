<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Services\SectionBuilderService;
use App\Services\SectionTypes\SectionType;
use App\Services\SectionTypes\SectionTypeRegistry;
use App\Services\SettingsService;
use App\Support\SectionMotion;
use Database\Seeders\EkdantaSiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/** The Ekdanta section pack: every type draws with its design defaults and its motion survives the sanitiser. */
class EkdantaSectionsTest extends TestCase
{
    use RefreshDatabase;

    /** @return Collection<string, SectionType> */
    private function types(): Collection
    {
        return app(SectionTypeRegistry::class)->all()->filter(fn (SectionType $type) => $type->group() === 'Ekdanta')->keyBy(fn (SectionType $type) => $type->key());
    }

    private function page(string $slug, array $types): Page
    {
        $page = Page::create(['title' => ucfirst($slug), 'slug' => $slug, 'status' => Page::STATUS_PUBLISHED]);
        foreach ($types as $type) {
            app(SectionBuilderService::class)->addSection($page, 'page', $type);
        }

        return $page;
    }

    public function test_every_motion_default_is_valid_as_written(): void
    {
        $presets = SectionMotion::presets();

        foreach ($this->types() as $key => $type) {
            $defaults = $type->motionDefaults();
            $this->assertNotSame([], $defaults, "{$key} has no motion defaults");
            // Anything the sanitiser drops or changes is a typo in resources/motion/ekdanta.
            $this->assertEquals($defaults, SectionMotion::sanitiseStored($defaults), "{$key}: motion defaults change when sanitised");

            foreach ($defaults as $device) {
                foreach ((array) ($device['tracks'] ?? []) as $id => $track) {
                    if (isset($track['preset'])) {
                        $this->assertArrayHasKey($track['preset'], $presets, "{$key}.{$id}: unknown preset");
                    }
                }
            }
        }
    }

    public function test_every_section_draws_with_its_defaults(): void
    {
        (new EkdantaSiteSeeder)->briefForm(); // the Brief Builder draws a Forms module form
        $types = $this->types()->keys()->all();
        $this->assertGreaterThanOrEqual(8, count($types));
        $page = $this->page('ek-all', $types);

        $html = $this->get('/ek-all')->assertOk()->getContent();

        foreach ($page->sections as $section) {
            $this->assertNotNull(SectionMotion::render($section), "{$section->type} renders no motion");
        }
        $this->assertSame(count($types), substr_count($html, 'data-ek-config'));
    }

    public function test_the_finale_reads_the_business_settings_and_replaces_the_footer(): void
    {
        app(SettingsService::class)->updateGroup('business', [
            'email' => 'info@ekdanta.in',
            'country_code' => '91',
            'phones' => [
                ['label' => 'All services', 'number' => '90994 17987', 'whatsapp' => true],
                ['label' => 'Sales', 'number' => '63544 33194'],
            ],
            'offices' => [['name' => 'Surat', 'address' => 'Bhatar Trade Center, Surat']],
        ]);
        $this->page('closing', ['ekdanta-finale']);

        $this->get('/closing')->assertOk()
            ->assertSee('id="contact"', false)
            ->assertSee('href="tel:+916354433194"', false)
            ->assertSee('Sales')
            ->assertSee('mailto:info@ekdanta.in', false)
            ->assertSee('Bhatar Trade Center, Surat')
            ->assertSee('href="https://wa.me/919099417987"', false)
            ->assertSee('© '.now()->format('Y').' Ekdanta Branding and Marketing.', false)
            ->assertSee('data-ek-pause', false)
            ->assertDontSee('<footer class="gsm-footer', false);
    }

    public function test_the_404_page_finds_real_pages_and_reports_the_missing_address(): void
    {
        app(SettingsService::class)->updateGroup('business', ['country_code' => '91', 'phones' => [['label' => 'All services', 'number' => '90994 17987', 'whatsapp' => true]]]);
        $lost = $this->page('lost', ['ekdanta-not-found-hero', 'ekdanta-page-finder', 'ekdanta-report-link']);
        \App\Support\PageRoles::assign($lost, 'not_found');
        $this->page('about-us', []);
        Page::create(['title' => 'Secret draft', 'slug' => 'secret', 'status' => Page::STATUS_DRAFT]);

        $html = $this->get('/no-such-page')->assertNotFound()->getContent();
        preg_match('/<script type="application\/json" data-finder-index>(.*?)<\/script>/s', $html, $m);
        $titles = array_column(json_decode($m[1], true), 't');

        $this->assertContains('About-us', $titles);
        $this->assertNotContains('Secret draft', $titles, 'drafts stay out');
        $this->assertNotContains('Lost', $titles, 'the 404 page itself is not a destination');
        $this->assertStringContainsString(rawurlencode(' '.url('/no-such-page')).'"', $html, 'the WhatsApp report names the address that failed');
    }

    public function test_the_brief_builder_walks_the_ekdanta_brief_form(): void
    {
        (new EkdantaSiteSeeder)->briefForm();
        $this->page('contact', ['ekdanta-brief-builder']);

        $html = $this->get('/contact')->assertOk()
            ->assertSee('action="'.route('front.form.submit', 'ekdanta-brief').'"', false)
            ->assertSee('What do you need help with?')
            ->assertSee('data-wa="I would like help with: {value}."', false)
            ->assertSee('data-autofill="utm_source"', false)
            ->assertSee('Sent it? See what happens next')
            ->getContent();

        // Four steps per composition; chips are real inputs with labels.
        $this->assertSame(8, substr_count($html, 'data-brief-step='));
        $this->assertStringContainsString('<input class="ek-brief-check" type="checkbox" name="services[]" value="Branding"', $html);
    }
}
