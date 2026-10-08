<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use App\Support\SectionAnimation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The animation engine: settings in, data-* attributes out, and the
 * guarantees that keep a broken animation from breaking the page.
 */
class SectionAnimationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (config('gsm.roles') as $role) {
            Role::findOrCreate($role, 'web');
        }

        foreach (['pages.manage', 'developer.mode'] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    private function pageWithSection(array $style = []): Page
    {
        $page = Page::create([
            'title' => 'Animated', 'slug' => 'animated',
            'status' => Page::STATUS_PUBLISHED, 'published_at' => now(),
        ]);

        $page->sections()->create([
            'type' => 'hero',
            'name' => 'Hero',
            'sort_order' => 0,
            'is_active' => true,
            'data_desktop' => ['heading' => 'Animated heading'],
            'style' => $style,
        ]);

        return $page->refresh();
    }

    /* ── Settings ────────────────────────────────────────────────── */

    public function test_defaults_apply_when_nothing_is_set(): void
    {
        $anim = SectionAnimation::forDevice([], 'desktop');

        $this->assertSame('none', $anim['preset']);
        $this->assertSame(700, $anim['duration']);
        $this->assertTrue($anim['once']);
    }

    public function test_tablet_and_mobile_inherit_desktop_until_they_are_set(): void
    {
        $style = ['anim' => ['desktop' => ['preset' => 'fade-up', 'duration' => 900]]];

        $this->assertSame('fade-up', SectionAnimation::forDevice($style, 'tablet')['preset']);
        $this->assertSame(900, SectionAnimation::forDevice($style, 'mobile')['duration']);

        $style['anim']['mobile'] = ['preset' => 'fade-in', 'duration' => 400];

        $this->assertSame('fade-in', SectionAnimation::forDevice($style, 'mobile')['preset']);
        $this->assertSame('fade-up', SectionAnimation::forDevice($style, 'desktop')['preset'], 'desktop untouched');
    }

    /**
     * The path a non-designer actually takes: open a section, pick an effect
     * on the Desktop tab, save. The other tabs are submitted untouched, and
     * they must not silently switch phones and tablets off.
     */
    public function test_choosing_an_effect_on_desktop_alone_animates_every_device(): void
    {
        $submitted = [
            'desktop' => ['preset' => 'fade-up', 'duration' => '800', 'ease' => 'outQuad', 'delay' => '0', 'stagger' => '0', 'once' => true],
            'tablet' => ['preset' => SectionAnimation::INHERIT],
            'mobile' => ['preset' => SectionAnimation::INHERIT],
        ];

        $style = ['anim' => SectionAnimation::sanitiseAll($submitted)];

        $this->assertSame('fade-up', SectionAnimation::forDevice($style, 'mobile')['preset']);
        $this->assertSame(800, SectionAnimation::forDevice($style, 'tablet')['duration']);

        // No per-device attributes: the one set covers all three screens.
        $attributes = SectionAnimation::attributesFor($style);

        $this->assertSame('fade-up', $attributes['data-anim']);
        $this->assertArrayNotHasKey('data-anim-mobile', $attributes);
        $this->assertArrayNotHasKey('data-anim-tablet', $attributes);
    }

    public function test_an_inheriting_device_follows_a_later_desktop_change(): void
    {
        $style = ['anim' => SectionAnimation::sanitiseAll([
            'desktop' => ['preset' => 'fade-up'],
            'mobile' => ['preset' => SectionAnimation::INHERIT],
        ])];

        // Only the intent is stored, not a frozen copy of the numbers.
        $this->assertSame(['preset' => 'inherit'], $style['anim']['mobile']);

        $style['anim']['desktop']['preset'] = 'zoom-in';

        $this->assertSame('zoom-in', SectionAnimation::forDevice($style, 'mobile')['preset']);
    }

    public function test_a_device_can_still_opt_out_entirely(): void
    {
        $style = ['anim' => SectionAnimation::sanitiseAll([
            'desktop' => ['preset' => 'flip-up'],
            'mobile' => ['preset' => 'none'],
        ])];

        $this->assertSame('none', SectionAnimation::forDevice($style, 'mobile')['preset']);
        $this->assertSame('none', SectionAnimation::attributesFor($style)['data-anim-mobile']);
    }

    public function test_the_editor_offers_same_as_desktop_on_the_device_tabs(): void
    {
        $page = $this->pageWithSection(['anim' => ['desktop' => ['preset' => 'fade-up']]]);

        $html = $this->actingAs($this->admin())
            ->get("/cms/pages/{$page->id}/edit")
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Same as desktop', $html);
        $this->assertStringContainsString('value="inherit"', $html);
    }

    public function test_a_pre_upgrade_animation_setting_still_plays(): void
    {
        // Sections saved before v0.3 stored a single string.
        $anim = SectionAnimation::forDevice(['animation' => 'zoom-in'], 'desktop');

        $this->assertSame('zoom-in', $anim['preset']);
    }

    /** @dataProvider hostileValues */
    public function test_hostile_values_are_clamped_or_dropped(array $values, string $key, mixed $expected): void
    {
        $this->assertSame($expected, SectionAnimation::sanitise($values)[$key]);
    }

    public static function hostileValues(): array
    {
        return [
            'unknown preset falls back' => [['preset' => 'explode'], 'preset', 'none'],
            'unknown easing falls back' => [['ease' => 'javascript:alert(1)'], 'ease', 'outQuad'],
            'duration below range' => [['duration' => 1], 'duration', 150],
            'duration above range' => [['duration' => 999999], 'duration', 2500],
            'negative delay' => [['delay' => -500], 'delay', 0],
            'stagger above range' => [['stagger' => 5000], 'stagger', 400],
            'non-numeric duration' => [['duration' => 'fast'], 'duration', 700],
        ];
    }

    /* ── Attributes ──────────────────────────────────────────────── */

    public function test_an_unanimated_section_carries_no_attributes(): void
    {
        $this->assertSame([], SectionAnimation::attributesFor([]));
        $this->assertSame([], SectionAnimation::attributesFor(['anim' => ['desktop' => ['preset' => 'none']]]));
    }

    public function test_attributes_describe_the_desktop_settings(): void
    {
        $attributes = SectionAnimation::attributesFor([
            'anim' => ['desktop' => ['preset' => 'fade-up', 'duration' => 800, 'delay' => 100, 'ease' => 'outBack', 'stagger' => 80, 'once' => false]],
        ]);

        $this->assertSame('fade-up', $attributes['data-anim']);
        $this->assertSame('800', $attributes['data-anim-duration']);
        $this->assertSame('100', $attributes['data-anim-delay']);
        $this->assertSame('outBack', $attributes['data-anim-ease']);
        $this->assertSame('80', $attributes['data-anim-stagger']);
        $this->assertSame('0', $attributes['data-anim-once']);
    }

    public function test_a_device_that_matches_desktop_adds_no_extra_attributes(): void
    {
        $attributes = SectionAnimation::attributesFor(['anim' => ['desktop' => ['preset' => 'fade-up']]]);

        $this->assertArrayNotHasKey('data-anim-tablet', $attributes);
        $this->assertArrayNotHasKey('data-anim-mobile', $attributes);
    }

    public function test_a_different_mobile_effect_gets_its_own_attributes(): void
    {
        $attributes = SectionAnimation::attributesFor([
            'anim' => [
                'desktop' => ['preset' => 'zoom-in', 'duration' => 900],
                'mobile' => ['preset' => 'fade-in', 'duration' => 300],
            ],
        ]);

        $this->assertSame('zoom-in', $attributes['data-anim']);
        $this->assertSame('fade-in', $attributes['data-anim-mobile']);
        $this->assertSame('300', $attributes['data-anim-mobile-duration']);
    }

    /* ── Rendering ───────────────────────────────────────────────── */

    public function test_the_page_renders_the_attributes_and_loads_the_engine(): void
    {
        $this->pageWithSection(['anim' => ['desktop' => ['preset' => 'fade-up', 'stagger' => 80]]]);

        $html = $this->get('/animated')->assertOk()->getContent();

        $this->assertStringContainsString('data-anim="fade-up"', $html);
        $this->assertStringContainsString('data-anim-stagger="80"', $html);
        $this->assertStringContainsString('anime.umd.min.js', $html);
        $this->assertStringContainsString('js/gsm-animations.js', $html);
        $this->assertStringContainsString("classList.add('js-enabled')", $html);
    }

    public function test_an_unanimated_page_renders_no_anim_attributes(): void
    {
        $this->pageWithSection();

        $html = $this->get('/animated')->assertOk()->getContent();

        $this->assertStringNotContainsString('data-anim=', $html);
        $this->assertStringContainsString('Animated heading', $html, 'content still renders');
    }

    public function test_the_library_is_pinned_with_an_integrity_hash(): void
    {
        $this->pageWithSection(['anim' => ['desktop' => ['preset' => 'fade-in']]]);

        $html = $this->get('/animated')->assertOk()->getContent();

        $this->assertStringContainsString('integrity="sha384-', $html);
        $this->assertStringContainsString('crossorigin="anonymous"', $html);
    }

    public function test_editor_and_live_still_render_identically(): void
    {
        $this->pageWithSection(['anim' => ['desktop' => ['preset' => 'flip-up'], 'mobile' => ['preset' => 'fade-in']]]);

        // Compare the section tags: that is where the animation attributes,
        // classes and inline styles live. The preview also carries inline-edit
        // hooks a visitor never sees, which is deliberate and not a mismatch.
        $sectionTags = function (string $html): array {
            preg_match_all('/<section[^>]*>/i', $html, $matches);

            return array_map(fn (string $tag) => preg_replace('/\s+/', ' ', $tag), $matches[0]);
        };

        $live = $this->get('/animated')->assertOk()->getContent();
        $preview = $this->actingAs($this->admin())->get('/animated?preview=1')->assertOk()->getContent();

        $this->assertSame($sectionTags($live), $sectionTags($preview));
    }

    /* ── Saving through the builder ──────────────────────────────── */

    public function test_saving_animation_settings_from_the_builder(): void
    {
        $page = $this->pageWithSection();
        $section = $page->sections->first();

        $this->actingAs($this->admin())->putJson("/cms/sections/{$section->id}", [
            'device' => 'desktop',
            'data' => null,
            'style' => [
                'anim' => [
                    'desktop' => ['preset' => 'fade-up', 'duration' => '850', 'delay' => '120', 'ease' => 'outExpo', 'stagger' => '90', 'once' => true],
                    'mobile' => ['preset' => 'fade-in', 'duration' => '300'],
                ],
            ],
        ])->assertOk();

        $anim = $section->refresh()->style['anim'];

        $this->assertSame('fade-up', $anim['desktop']['preset']);
        $this->assertSame(850, $anim['desktop']['duration'], 'numeric strings are cast');
        $this->assertSame('fade-in', $anim['mobile']['preset']);
    }

    public function test_a_hostile_animation_payload_cannot_reach_storage(): void
    {
        $page = $this->pageWithSection();
        $section = $page->sections->first();

        $this->actingAs($this->admin())->putJson("/cms/sections/{$section->id}", [
            'device' => 'desktop',
            'data' => null,
            'style' => [
                'anim' => [
                    'desktop' => [
                        'preset' => '"><script>alert(1)</script>',
                        'ease' => 'javascript:alert(1)',
                        'duration' => 999999,
                    ],
                ],
            ],
        ])->assertOk();

        $anim = $section->refresh()->style['anim']['desktop'];

        $this->assertSame('none', $anim['preset']);
        $this->assertSame('outQuad', $anim['ease']);
        $this->assertSame(2500, $anim['duration']);

        // And nothing hostile reaches the page.
        $html = $this->get('/animated')->assertOk()->getContent();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }
}
