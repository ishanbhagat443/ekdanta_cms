<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ButtonStyle;
use App\Models\Gradient;
use App\Models\User;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(config('gsm.roles.super_admin'), 'web');
        Permission::findOrCreate('theme.manage', 'web');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(config('gsm.roles.super_admin'));

        return $user;
    }

    /* ── Gradient Library ───────────────────────────────────────── */

    public function test_gradient_generates_valid_multi_stop_css(): void
    {
        $gradient = Gradient::create([
            'name' => 'Primary', 'slug' => 'primary', 'type' => 'linear', 'angle' => 90,
            'stops' => [
                ['color' => '#F03225', 'opacity' => 100, 'position' => 100], // out of order on purpose
                ['color' => '#981E63', 'opacity' => 100, 'position' => 2],
                ['color' => '#B14D86', 'opacity' => 100, 'position' => 19],
                ['color' => '#F5791A', 'opacity' => 100, 'position' => 50],
            ],
        ]);

        $this->assertSame(
            'linear-gradient(90deg, #981E63 2%, #B14D86 19%, #F5791A 50%, #F03225 100%)',
            $gradient->toCss()
        );
    }

    public function test_gradient_opacity_and_radial_conic_types(): void
    {
        $radial = Gradient::create([
            'name' => 'Glow', 'slug' => 'glow', 'type' => 'radial', 'position' => '50% 25%',
            'stops' => [
                ['color' => '#ffffff', 'opacity' => 40, 'position' => 0],
                ['color' => '#000000', 'opacity' => 100, 'position' => 100],
            ],
        ]);
        $this->assertSame('radial-gradient(circle at 50% 25%, rgba(255,255,255,0.4) 0%, #000000 100%)', $radial->toCss());

        $conic = Gradient::create([
            'name' => 'Wheel', 'slug' => 'wheel', 'type' => 'conic', 'angle' => 45,
            'stops' => [
                ['color' => '#ff0000', 'opacity' => 100, 'position' => 0],
                ['color' => '#0000ff', 'opacity' => 100, 'position' => 100],
            ],
        ]);
        $this->assertStringStartsWith('conic-gradient(from 45deg at center,', $conic->toCss());
    }

    public function test_gradient_crud_publishes_css_variable_and_utility(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/cms/gradients', [
            'name' => 'Sunset Gradient',
            'type' => 'linear',
            'angle' => 45,
            'is_active' => 1,
            'stops' => [
                ['color' => '#F5791A', 'opacity' => 100, 'position' => 0],
                ['color' => '#981E63', 'opacity' => 100, 'position' => 100],
            ],
        ])->assertRedirect(route('admin.gradients.index'));

        $css = app(ThemeService::class)->renderCss();
        $this->assertStringContainsString('--gsm-gradient-sunset-gradient:linear-gradient(45deg, #F5791A 0%, #981E63 100%);', $css);
        $this->assertStringContainsString('.gsm-bg-gradient-sunset-gradient{background-image:var(--gsm-gradient-sunset-gradient);}', $css);

        // Fewer than 2 stops rejected.
        $this->actingAs($admin)->post('/cms/gradients', [
            'name' => 'Bad', 'type' => 'linear',
            'stops' => [['color' => '#000000', 'opacity' => 100, 'position' => 0]],
        ])->assertSessionHasErrors('stops');
    }

    public function test_gradient_routes_require_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user)->get('/cms/gradients')->assertForbidden();
        $this->actingAs($this->admin())->get('/cms/gradients')->assertOk();
        $this->actingAs($this->admin())->get('/cms/gradients/create')->assertOk();
    }

    /* ── Button Builder ─────────────────────────────────────────── */

    public function test_button_style_compiles_full_css_rule_set(): void
    {
        Gradient::create([
            'name' => 'CTA', 'slug' => 'cta', 'type' => 'linear', 'angle' => 90,
            'stops' => [
                ['color' => '#C2185B', 'opacity' => 100, 'position' => 0],
                ['color' => '#F7941D', 'opacity' => 100, 'position' => 100],
            ],
        ]);

        ButtonStyle::create([
            'name' => 'Primary Pill', 'slug' => 'primary-pill', 'is_active' => true,
            'config' => array_replace_recursive(ButtonStyle::defaults(), [
                'typography' => ['transform' => 'uppercase'],
                'padding' => ['mobile' => ['top' => 8, 'right' => 16, 'bottom' => 8, 'left' => 16]],
                'border' => ['radius' => ['tl' => '999px', 'tr' => '8px', 'br' => '50%', 'bl' => '0px']],
                'background' => ['mode' => 'gradient', 'gradient_slug' => 'cta'],
                'hover' => ['mode' => 'solid', 'color' => '#ffffff', 'text_color' => '#101828'],
                'animation' => ['hover_effect' => 'scale', 'duration' => 300],
                'shadows' => [
                    ['x' => 0, 'y' => 4, 'blur' => 10, 'spread' => 0, 'color' => '#000000', 'opacity' => 20, 'inset' => false],
                    ['x' => 0, 'y' => 1, 'blur' => 2, 'spread' => 0, 'color' => '#ffffff', 'opacity' => 100, 'inset' => true],
                ],
            ]),
        ]);

        $css = app(ThemeService::class)->renderCss();

        $this->assertStringContainsString('.gsm-btn-primary-pill{', $css);
        $this->assertStringContainsString('text-transform:uppercase;', $css);
        $this->assertStringContainsString('border-top-left-radius:999px;', $css);      // per-corner, free values
        $this->assertStringContainsString('border-bottom-right-radius:50%;', $css);
        $this->assertStringContainsString('background-image:var(--gsm-gradient-cta);', $css); // library reuse
        $this->assertStringContainsString('@media (max-width: 767.98px){.gsm-btn-primary-pill{padding-top:8px;', $css);
        $this->assertStringContainsString('.gsm-btn-primary-pill:hover{', $css);
        $this->assertStringContainsString('transform:scale(1.05);', $css);
        $this->assertStringContainsString('box-shadow:0px 4px 10px 0px rgba(0,0,0,0.2), inset 0px 1px 2px 0px #ffffff;', $css); // unlimited shadows
        $this->assertStringContainsString('transition:all 300ms ease;', $css);
    }

    public function test_gsm_button_component_renders_style_class_icons_and_fallback(): void
    {
        ButtonStyle::create([
            'name' => 'Iconic', 'slug' => 'iconic', 'is_active' => true,
            'config' => array_replace_recursive(ButtonStyle::defaults(), [
                'icon' => ['left' => 'fas fa-star', 'right' => 'fas fa-arrow-right', 'gap' => 10],
                'animation' => ['hover_effect' => 'ripple'],
            ]),
        ]);

        $html = Blade::render('<x-gsm-button url="/collection" label="Explore" style="iconic" />');
        $this->assertStringContainsString('gsm-btn-iconic', $html);
        $this->assertStringContainsString('href="/collection"', $html);
        $this->assertStringContainsString('fas fa-star', $html);
        $this->assertStringContainsString('fas fa-arrow-right', $html);
        $this->assertStringContainsString('data-gsm-ripple', $html);

        // Unknown slug → legacy theme button fallback, link intact.
        $fallback = Blade::render('<x-gsm-button url="/x" label="Go" style="deleted-style" />');
        $this->assertStringContainsString('gsm-btn-gradient', $fallback);
        $this->assertStringContainsString('Go', $fallback);
    }

    public function test_global_button_custom_css_saves_and_emits_after_button_rules(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/cms/theme-customizer/buttons', [
            'custom_css' => '.btn-primary{border-radius:40px;background:linear-gradient(90deg,#981E63,#F03225);}',
        ])->assertRedirect(route('admin.theme.index'));

        $css = app(ThemeService::class)->renderCss();

        $this->assertStringContainsString('.btn-primary{border-radius:40px;', $css);

        // Emitted AFTER the generated button rules so admin CSS wins the cascade.
        $this->assertGreaterThan(
            strpos($css, '.gsm-btn-theme{'),
            strpos($css, '.btn-primary{border-radius:40px;'),
            'Button custom CSS must come after generated button CSS'
        );

        // Oversized payload rejected by the schema-driven validation.
        $this->actingAs($admin)->put('/cms/theme-customizer/buttons', [
            'custom_css' => str_repeat('x', 20001),
        ])->assertSessionHasErrors('custom_css');
    }

    public function test_button_style_admin_crud_and_duplicate(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/cms/button-styles')->assertOk();
        $this->actingAs($admin)->get('/cms/button-styles/create')->assertOk();

        $this->actingAs($admin)->post('/cms/button-styles', [
            'name' => 'Hero CTA',
            'is_active' => 1,
            'config' => array_replace_recursive(ButtonStyle::defaults(), [
                'background' => ['mode' => 'solid', 'color' => '#c2185b'],
            ]),
        ])->assertRedirect(route('admin.button-styles.index'));

        $style = ButtonStyle::where('slug', 'hero-cta')->firstOrFail();

        // Invalid corner radius rejected.
        $this->actingAs($admin)->put("/cms/button-styles/{$style->id}", [
            'name' => 'Hero CTA',
            'config' => array_replace_recursive($style->config, [
                'border' => ['radius' => ['tl' => 'red;}']],
                'background' => ['mode' => 'solid'],
            ]),
        ])->assertSessionHasErrors('config.border.radius.tl');

        $this->actingAs($admin)->post("/cms/button-styles/{$style->id}/duplicate")->assertRedirect();
        $this->assertSame(2, ButtonStyle::count());
    }
}
