<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MotionPreset;
use App\Models\Page;
use App\Models\Section;
use App\Models\User;
use App\Support\SectionMotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Motion system v2: the Motion tab's storage and save rules, what the page
 * renders for the engine, the older settings still animating, and the
 * Motion Library.
 */
class MotionSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SectionMotion::forgetPresets();
        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        foreach (['pages.manage', 'motion.manage', 'developer.mode', 'builder.header'] as $permission) {
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

    private function section(array $style = [], string $type = 'text-block', array $data = ['heading' => 'Moving heading']): Section
    {
        $page = Page::firstOrCreate(['slug' => 'motion'], ['title' => 'Motion', 'status' => Page::STATUS_PUBLISHED]);

        return $page->sections()->create([
            'type' => $type, 'name' => 'S', 'sort_order' => $page->sections()->count(), 'is_active' => true,
            'data_desktop' => $data, 'style' => $style,
        ]);
    }

    private function configOf(string $html, int $sectionId): array
    {
        preg_match('#<section id="gsm-s'.$sectionId.'".*?<script type="application/json" data-ek-config>(.*?)</script>#s', $html, $m);

        return json_decode($m[1] ?? 'null', true) ?? [];
    }

    private function save(Section $section, array $payload, ?User $user = null)
    {
        return $this->actingAs($user ?? $this->superAdmin())
            ->putJson('/cms/sections/'.$section->id, ['device' => 'desktop', 'data' => null] + $payload);
    }

    /* ── The older settings keep working ─────────────────────────────── */

    public function test_old_scroll_animation_settings_render_as_an_enter_track(): void
    {
        $section = $this->section(['anim' => [
            'desktop' => ['preset' => 'fade-up', 'duration' => 900, 'delay' => 100, 'ease' => 'spring', 'stagger' => 0, 'once' => true],
            'tablet' => ['preset' => 'inherit'],
            'mobile' => ['preset' => 'none'],
        ]]);

        $html = $this->get('/motion')->assertOk()->getContent();
        $config = $this->configOf($html, $section->id);

        $this->assertStringContainsString('data-ek-motion', $html);
        $this->assertSame(['opacity' => [0, 1], 'translateY' => ['32px', 0]], $config['desktop']['tracks'][0]['keyframes'], 'screen px: moves exactly as before');
        $this->assertSame(900, $config['desktop']['tracks'][0]['duration']);
        $this->assertSame('spring', $config['desktop']['tracks'][0]['ease']);
        $this->assertSame($config['desktop']['tracks'], $config['tablet']['tracks'], 'tablet follows desktop');
        $this->assertArrayNotHasKey('tracks', $config['mobile'], '"none" on mobile switches it off there');
        // Until the script runs, the section starts hidden (no flash), but
        // never without JS, under reduced motion, or with motion switched off.
        $this->assertStringContainsString('html.js-enabled:not(.ek-reveal):not([data-motion="off"]) #gsm-s'.$section->id.':not(.ek-revealed){opacity:0}', $html);
        $this->assertStringContainsString('@media (prefers-reduced-motion: no-preference) and (min-width: 992px)', $html);
    }

    public function test_the_data_migration_turns_old_settings_into_motion_and_back(): void
    {
        $section = $this->section(['anim' => ['desktop' => ['preset' => 'zoom-in', 'stagger' => 80]]]);
        $hero = $this->section([], 'hero', ['heading' => 'Hero', 'entrance' => 'none']);
        $migration = require database_path('migrations/2026_10_09_000003_move_section_animations_into_motion.php');

        $migration->up();

        $motion = $section->fresh()->style['motion'];
        $this->assertSame('zoom-in', $motion['desktop']['tracks']['enter']['preset']);
        $this->assertTrue($motion['desktop']['tracks']['enter']['children'], 'stagger animates the children in turn');
        $this->assertFalse($hero->fresh()->style['motion']['desktop']['tracks']['content']['enabled'], 'Hero entrance "none" = content track off');
        $this->assertSame('zoom-in', $section->fresh()->style['anim']['desktop']['preset'], 'old values are kept');

        $migration->down();
        $this->assertArrayNotHasKey('motion', (array) $section->fresh()->style);
    }

    public function test_hero_content_entrance_is_a_track_with_its_css_preset(): void
    {
        $hero = $this->section([], 'hero', ['heading' => 'Hero heading']);
        $this->section([], 'hero', ['heading' => 'Second hero']);

        $html = $this->get('/motion')->assertOk()->getContent();
        $track = $this->configOf($html, $hero->id)['desktop']['tracks'][0];

        $this->assertSame(['id' => 'content', 'part' => 'content', 'trigger' => 'load', 'css' => 'ek-hero-fade-up', 'duration' => 900, 'delay' => 0, 'ease' => 'ease-out', 'stagger' => 0], $track);
        $this->assertSame(1, substr_count($html, '@keyframes ek-hero-fade-up{'), 'printed once per page');
        $this->assertStringContainsString('data-m="content"', $html);
    }

    /* ── Motion tab saves ────────────────────────────────────────────── */

    public function test_a_save_keeps_only_what_changed(): void
    {
        $section = $this->section([], 'hero', ['heading' => 'Hero']);
        $shown = SectionMotion::effective($section);

        $posted = $shown;
        $posted['desktop']['tracks']['content']['duration'] = 1200;
        $posted['desktop']['tracks']['content'] += ['enabled' => '1', 'once' => '1', 'children' => '0', 'reduce' => 'final'];
        $posted['tablet']['tracks']['content'] += ['enabled' => '1', 'once' => '1', 'children' => '0', 'reduce' => 'final'];

        $this->save($section, ['motion' => $posted])->assertOk();

        $this->assertSame(['desktop' => ['tracks' => ['content' => ['duration' => 1200]]]], $section->fresh()->style['motion']);
        $this->assertSame(1200, SectionMotion::effective($section->fresh())['tablet']['tracks']['content']['duration'], 'untouched tablet follows desktop');
    }

    public function test_emptying_a_field_and_reset_go_back_to_the_design(): void
    {
        $section = $this->section(['motion' => ['desktop' => ['tracks' => ['content' => ['duration' => 1200, 'delay' => 300]]]]], 'hero', ['heading' => 'Hero']);

        $posted = SectionMotion::effective($section);
        unset($posted['desktop']['tracks']['content']['duration']); // the field came back empty
        $this->save($section, ['motion' => ['desktop' => $posted['desktop']]])->assertOk();
        $this->assertSame(['desktop' => ['tracks' => ['content' => ['delay' => 300]]]], $section->fresh()->style['motion']);

        $this->save($section, ['motion_reset' => ['desktop.tracks.content']])->assertOk();
        $this->assertSame([], $section->fresh()->style['motion']);
    }

    public function test_a_new_track_on_desktop_reaches_phones_and_can_be_removed(): void
    {
        $section = $this->section();
        $this->save($section, ['motion' => ['desktop' => ['stage' => ['pin' => '1', 'length' => '2400'], 'tracks' => [
            'head' => ['part' => 'head', 'trigger' => 'scrub', 'preset' => 'rise', 'range' => ['-650', '-230'], 'params' => ['distance' => 60], 'enabled' => '1'],
        ]]]])->assertOk();

        $html = $this->get('/motion')->getContent();
        $config = $this->configOf($html, $section->id);
        $this->assertSame(['opacity' => [0, 1], 'translateY' => [60, 0]], $config['mobile']['tracks'][0]['keyframes'], 'placeholders filled, phones follow');
        $this->assertSame([-650, -230], $config['desktop']['tracks'][0]['range']);
        $this->assertStringContainsString('ek-pin-d', $html);
        $this->assertStringContainsString('--ek-len-m: 2400', $html);

        $this->save($section, ['motion_reset' => ['desktop.tracks.head']])->assertOk();
        $this->assertArrayNotHasKey('tracks', SectionMotion::effective($section->fresh())['desktop']);
    }

    public function test_unknown_presets_and_hostile_values_never_reach_the_page(): void
    {
        $section = $this->section();

        $this->save($section, ['motion' => ['desktop' => ['tracks' => ['x' => ['part' => 'head', 'preset' => 'no-such-thing']]]]])
            ->assertStatus(422)->assertJsonValidationErrors('motion.desktop.tracks.x.preset');

        $this->save($section, ['motion' => ['desktop' => ['tracks' => [
            'a' => ['part' => 'head', 'trigger' => 'enter', 'keyframes' => '{"backgroundImage": ["url(https://evil.test/x.png)", "none"]}'],
            'b' => ['part' => 'head"><script>', 'trigger' => 'enter', 'preset' => 'fade-in'],
            'c' => ['part' => 'head', 'trigger' => 'enter', 'keyframes' => '{"opacity": ["0;}</style><script>alert(1)</script>", 1]}'],
            'd' => ['part' => 'head', 'trigger' => 'enter', 'preset' => 'fade-in', 'duration' => '999999999', 'ease' => 'alert(1)'],
        ]]]])->assertOk();

        $tracks = $section->fresh()->style['motion']['desktop']['tracks'];
        $this->assertArrayNotHasKey('keyframes', $tracks['a']);
        $this->assertArrayNotHasKey('part', $tracks['b']);
        $this->assertArrayNotHasKey('keyframes', $tracks['c']);
        $this->assertSame(60000, $tracks['d']['duration']);
        $this->assertArrayNotHasKey('ease', $tracks['d']);
        $this->assertStringNotContainsString('evil.test', $this->get('/motion')->getContent());
    }

    public function test_scene_settings_keep_numbers_and_number_lists_only(): void
    {
        $this->assertSame(
            ['n' => 2, 'hero' => [-320, 0], 'scatter' => [[-470, 60, 0.42, -8], [-170, 230, 0.38, 6]], 'name' => 'ok'],
            SectionMotion::params(['n' => '2', 'hero' => [-320, 0], 'scatter' => [[-470, 60, 0.42, -8], [-170, 230, 0.38, 6]], 'name' => 'ok',
                'bad' => ['a' => 1], 'worse' => [['x']], 'deep' => [[[1]]], 'evil' => 'url(x)'])
        );
    }

    public function test_style_saves_cannot_write_motion_around_the_sanitiser(): void
    {
        $section = $this->section();
        $this->save($section, ['style' => ['motion' => ['desktop' => ['tracks' => ['x' => ['part' => 'head', 'keyframes' => ['opacity' => ['url(x)', 1]]]]]], 'anim' => 'junk']])->assertOk();

        $this->assertArrayNotHasKey('motion', (array) $section->fresh()->style);
        $this->assertArrayNotHasKey('anim', (array) $section->fresh()->style);
    }

    public function test_imported_style_has_its_motion_cleaned(): void
    {
        $style = \App\Models\Section::withoutPrivilegedStyle(['motion' => [
            'desktop' => ['tracks' => ['x' => ['part' => 'head', 'trigger' => 'enter', 'keyframes' => ['opacity' => ['url(x)', 1]], 'duration' => 5]]],
            'evil' => ['anything'],
        ]]);

        $this->assertSame(['desktop' => ['tracks' => ['x' => ['part' => 'head', 'trigger' => 'enter', 'duration' => 5]]]], $style['motion']);
    }

    public function test_page_editors_edit_section_motion(): void
    {
        $section = $this->section();
        $this->save($section, ['motion' => ['desktop' => ['tracks' => ['t1' => ['part' => '@self', 'preset' => 'fade-in']]]]], $this->editor('pages.manage'))->assertOk();
        $this->save($section, ['motion' => ['desktop' => ['off' => '1']]], $this->editor('builder.header'))->assertForbidden();
    }

    public function test_the_builder_shows_a_motion_tab(): void
    {
        $section = $this->section([], 'hero', ['heading' => 'Hero']);

        $this->actingAs($this->superAdmin())->get(route('admin.pages.edit', $section->sectionable))
            ->assertOk()
            ->assertSee('href="#tab_'.$section->id.'_motion"', false)
            ->assertSee('name="motion[desktop][tracks][content][part]"', false)
            ->assertSee('data-motion-scrub', false);
    }

    public function test_reduced_motion_and_the_pause_button(): void
    {
        $this->seed(\Database\Seeders\HeaderSeeder::class);
        \App\Models\Layout::query()->where('area', 'header')->first()
            ->forceFill(['settings' => ['nav_style' => ['desktop' => 'overlay', 'tablet' => 'overlay', 'mobile' => 'overlay'], 'overlay_pause_label' => 'Pause animations']])->save();
        $this->section();

        $html = $this->get('/motion')->getContent();
        $this->assertStringContainsString('data-ek-pause aria-pressed="false">Pause animations</button>', $html);
        $this->assertStringNotContainsString('data-motion-forced', $html);

        $this->get('/motion?reduce-motion=1')->assertSee('data-motion="off" data-motion-forced', false);
    }

    /* ── Motion Library ─────────────────────────────────────────────── */

    public function test_the_library_ships_the_entrance_and_design_presets(): void
    {
        foreach (['fade-in', 'fade-up', 'fade-down', 'fade-left', 'fade-right', 'zoom-in', 'zoom-out', 'flip-up', 'hero-fade-up', 'rise', 'flip-y', 'draw', 'count-up', 'word-highlight', 'marquee', 'orbit'] as $slug) {
            $this->assertDatabaseHas('motion_presets', ['slug' => $slug, 'is_system' => true]);
        }

        $this->actingAs($this->editor('pages.manage'))->get(route('admin.motion-presets.index'))->assertForbidden();
        $this->actingAs($this->editor('motion.manage'))->get(route('admin.motion-presets.index'))->assertOk()->assertSee('Word highlight');
    }

    public function test_presets_are_created_checked_and_used(): void
    {
        $admin = $this->editor('motion.manage');

        $this->actingAs($admin)->post(route('admin.motion-presets.store'), [
            'name' => 'Big rise', 'kind' => 'anime', 'trigger' => 'scrub', 'duration' => 1000, 'ease' => 'outCubic',
            'keyframes' => '{"opacity": [0, 1], "translateY": ["{distance}", 0]}', 'params' => '{"distance": 120}',
        ])->assertRedirect();
        $this->assertDatabaseHas('motion_presets', ['slug' => 'big-rise', 'is_system' => false]);

        $this->actingAs($admin)->post(route('admin.motion-presets.store'), [
            'name' => 'Bad', 'kind' => 'css', 'trigger' => 'scrub', 'duration' => 1000, 'ease' => 'linear',
            'keyframes' => '{"from": {"opacity": "0"}, "to": {"opacity": "1"}}',
        ])->assertSessionHasErrors('trigger');

        $this->actingAs($admin)->post(route('admin.motion-presets.store'), [
            'name' => 'Worse', 'kind' => 'css', 'trigger' => 'loop', 'duration' => 1000, 'ease' => 'linear',
            'keyframes' => '{"from": {"background": "url(https://evil.test/x)"}}',
        ])->assertSessionHasErrors('keyframes');

        $section = $this->section(['motion' => ['desktop' => ['tracks' => ['h' => ['part' => 'head', 'trigger' => 'scrub', 'preset' => 'big-rise', 'range' => [0, 400]]]]]]);
        $this->assertSame([120, 0], $this->configOf($this->get('/motion')->getContent(), $section->id)['desktop']['tracks'][0]['keyframes']['translateY']);
    }

    public function test_built_in_presets_are_kept_and_copies_are_free(): void
    {
        $admin = $this->editor('motion.manage');
        $builtIn = MotionPreset::where('slug', 'fade-up')->first();

        $this->actingAs($admin)->delete(route('admin.motion-presets.destroy', $builtIn))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.motion-presets.duplicate', $builtIn))->assertRedirect();

        $copy = MotionPreset::where('slug', 'fade-up-copy')->firstOrFail();
        $this->assertFalse($copy->is_system);
        $this->actingAs($admin)->delete(route('admin.motion-presets.destroy', $copy))->assertRedirect();
    }

    public function test_export_and_import_round_trip(): void
    {
        $admin = $this->editor('motion.manage');
        MotionPreset::create(['name' => 'Mine', 'slug' => 'mine', 'kind' => 'anime', 'trigger' => 'enter', 'keyframes' => ['opacity' => [0, 1]], 'duration' => 500, 'ease' => 'outQuad']);

        $export = $this->actingAs($admin)->get(route('admin.motion-presets.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString('"slug": "mine"', $export);

        MotionPreset::where('slug', 'mine')->delete();
        $data = json_decode($export, true);
        $data['motion_presets'][] = ['name' => 'Fade up', 'slug' => 'fade-up', 'kind' => 'anime', 'trigger' => 'enter', 'keyframes' => ['opacity' => [1, 0]], 'duration' => 1, 'ease' => 'linear'];

        $this->actingAs($admin)->post(route('admin.motion-presets.import'), [
            'file' => UploadedFile::fake()->createWithContent('motion.json', json_encode($data)),
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('motion_presets', ['slug' => 'mine', 'duration' => 500]);
        $this->assertSame([0, 1], MotionPreset::where('slug', 'fade-up')->first()->keyframes['opacity'], 'built-in presets are never overwritten');

        $bad = ['motion_presets' => [['name' => 'X', 'kind' => 'anime', 'trigger' => 'enter', 'keyframes' => ['opacity' => ['expression(alert(1))', 1]], 'duration' => 1, 'ease' => 'linear']]];
        $this->actingAs($admin)->post(route('admin.motion-presets.import'), [
            'file' => UploadedFile::fake()->createWithContent('bad.json', json_encode($bad)),
        ])->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('motion_presets', ['name' => 'X']);
    }

    public function test_the_preset_preview_runs_the_real_engine(): void
    {
        $preset = MotionPreset::where('slug', 'word-highlight')->first();

        $html = $this->actingAs($this->editor('motion.manage'))->get(route('admin.motion-presets.preview', $preset))->assertOk()->getContent();

        $this->assertStringContainsString('data-ek-config', $html);
        $this->assertStringContainsString('"$split":"words"', $html);
        $this->assertStringContainsString('ek-pin-d', $html);
        $this->assertStringContainsString('<script type="module"', $html, 'the frontend bundle, engine included');
    }
}
