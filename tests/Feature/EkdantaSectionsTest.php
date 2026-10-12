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

    public function test_the_case_study_template_shows_its_case_and_links_to_the_next(): void
    {
        (new EkdantaSiteSeeder)->collections();
        $page = $this->page('case-template', ['ekdanta-case-hero', 'ekdanta-case-overview', 'ekdanta-next-case']);
        $page->forceFill(['settings' => ['template_for' => 'case_study']])->save();

        $this->get('/work/yantra-design')->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Yantra Design')
            ->assertSee('INDUSTRIAL MACHINERY')
            ->assertSee('Case study 02 of 06')
            ->assertSee('href="'.url('/work/heaven-solar').'"', false) // next case
            ->assertSee('href="'.url('/work/trilok-infratech').'"', false); // previous case
        $this->get('/work/no-such-case')->assertNotFound();
    }

    public function test_the_service_template_shows_its_service_questions_and_partners(): void
    {
        (new EkdantaSiteSeeder)->collections();
        $page = $this->page('service-template', ['ekdanta-service-hero', 'ekdanta-whats-included', 'ekdanta-service-pricing-faq', 'ekdanta-works-well-with']);
        $page->forceFill(['settings' => ['template_for' => 'service']])->save();

        $this->get('/services/meta-ads')->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Pixel and tracking setup')
            ->assertSee('Is the ad budget part of your fee?') // its own FAQ group
            ->assertDontSee('Do you shoot the content too?') // another service's FAQ
            ->assertSee('<details', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('href="'.url('/services/social-media-management').'"', false); // works well with
        $this->get('/services/brand-strategy')->assertNotFound(); // a real service without its own page
    }

    public function test_the_blog_grid_filters_by_topic_and_search_without_js(): void
    {
        (new EkdantaSiteSeeder)->samplePosts();
        $drafts = \App\Models\Post::query()->where('status', \App\Models\Post::STATUS_DRAFT)->pluck('title');
        $this->assertCount(10, $drafts, 'a real site gets drafts');
        $this->assertTrue($drafts->every(fn ($title) => str_starts_with($title, 'SAMPLE – ')));

        \App\Models\Post::query()->forceDelete();
        (new EkdantaSiteSeeder)->samplePosts(true);
        $this->page('notes', ['ekdanta-featured-article', 'ekdanta-article-grid']);
        $cards = function (string $url): array {
            preg_match_all('/<li data-m="ag-card"([^>]*)>/', $this->get($url)->assertOk()->getContent(), $m);

            return [count($m[1]), count(array_filter($m[1], fn ($attrs) => ! preg_match('/"\s+hidden\s+style=/', $attrs)))];
        };

        $this->assertSame([18, 18], $cards('/notes'), 'nine cards in each layout: the featured article stays above');
        $this->assertSame([18, 2], $cards('/notes?topic=ads'), 'only the Ads article is shown');
        $this->get('/notes?search=leads')->assertOk()->assertSee('Results for “leads”');
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
