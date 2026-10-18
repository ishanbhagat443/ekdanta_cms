<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Analytics and ad tags wait for the visitor's "Allow all": with no choice,
 * or "Only necessary", the HTML of / and /thank-you holds none of them.
 */
class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    /** Every tag the site can add, by something unique in its markup. */
    private const TAGS = [
        'Analytics' => 'googletagmanager.com/gtag/js?id=G-TEST1234',
        'Tag Manager' => 'googletagmanager.com/gtm.js',
        'Tag Manager (no JS)' => 'googletagmanager.com/ns.html?id=GTM-TEST12',
        'Meta Pixel' => "fbq('init',\"123456789012345\")",
        'Google Ads' => 'gtag/js?id=AW-987654321',
        'Ads conversion' => "send_to:\"AW-987654321\\/AbC-12_x\"",
    ];

    protected function setUp(): void
    {
        parent::setUp();
        app(SettingsService::class)->updateGroup('seo', ['google_analytics' => 'G-TEST1234', 'google_tag_manager' => 'GTM-TEST12']);
        Page::create(['title' => 'Home', 'slug' => 'home', 'status' => Page::STATUS_PUBLISHED]);
        Page::create(['title' => 'Thank you', 'slug' => 'thank-you', 'status' => Page::STATUS_PUBLISHED,
            'settings' => ['conversions' => ['meta_pixel' => '123456789012345', 'ads_id' => 'AW-987654321', 'ads_label' => 'AbC-12_x']]]);
    }

    private function tagsIn(string $html): array
    {
        return array_keys(array_filter(self::TAGS, fn (string $needle) => str_contains($html, $needle)));
    }

    public function test_no_tag_loads_before_allow_all_and_the_bar_asks(): void
    {
        foreach (['/', '/thank-you'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame([], $this->tagsIn($html), "{$url} before a choice");
            $this->assertStringNotContainsString('gtag(', $html);
            $this->assertMatchesRegularExpression('/<div class="gsm-cookie-bar" role="region" aria-label="Cookie choices" data-cookie-bar data-reload="1"\s*>/', $html);
            $this->assertStringContainsString('<b>Cookies, only with your OK.</b>', $html);
            $this->assertStringContainsString('<a href="/privacy-policy">Privacy Policy</a>', $html);
        }

        foreach (['/', '/thank-you'] as $url) {
            $html = $this->withUnencryptedCookie('cookie_consent', 'necessary')->get($url)->getContent();
            $this->assertSame([], $this->tagsIn($html), "{$url} with only necessary cookies");
            $this->assertMatchesRegularExpression('/data-cookie-bar data-reload="1"\s*hidden\s*>/', $html, 'the choice is made');
        }
    }

    public function test_allow_all_loads_every_tag_behind_consent_mode(): void
    {
        $home = $this->withUnencryptedCookie('cookie_consent', 'all')->get('/')->getContent();
        $this->assertSame(['Analytics', 'Tag Manager', 'Tag Manager (no JS)'], $this->tagsIn($home));
        $this->assertStringContainsString("gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied'});gtag('consent','update',{ad_storage:'granted'", $home);
        $this->assertLessThan(strpos($home, 'gtm.js'), strpos($home, "gtag('consent','default'"), 'consent mode first');

        $thanks = $this->withUnencryptedCookie('cookie_consent', 'all')->get('/thank-you')->getContent();
        $this->assertSame(array_keys(self::TAGS), $this->tagsIn($thanks));

        // Never in the builder's preview.
        $this->assertSame([], $this->tagsIn($this->withUnencryptedCookie('cookie_consent', 'all')->get('/thank-you?preview=1')->getContent()));
    }

    public function test_with_the_bar_switched_off_tags_load_as_before(): void
    {
        app(SettingsService::class)->updateGroup('cookies', ['enabled' => '0']);

        $html = $this->get('/thank-you')->getContent();
        $this->assertSame(array_keys(self::TAGS), $this->tagsIn($html));
        $this->assertStringNotContainsString('data-cookie-bar', $html);
        $this->assertStringNotContainsString("gtag('consent'", $html);
    }
}
