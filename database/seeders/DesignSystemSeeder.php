<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ButtonStyle;
use App\Models\Gradient;
use App\Services\ButtonStyleService;
use App\Services\GradientService;
use Illuminate\Database\Seeder;

/**
 * Design-system defaults: Gradient Library entries + Button Builder styles.
 * Idempotent (firstOrCreate by slug) — admin-edited rows are never touched.
 */
class DesignSystemSeeder extends Seeder
{
    public function run(): void
    {
        $gradients = [
            [
                'slug' => 'primary-gradient',
                'name' => 'Primary Gradient',
                'description' => 'Brand magenta → orange sweep used on primary CTAs (design reference).',
                'type' => 'linear', 'angle' => 90, 'stops' => [
                    ['color' => '#14203a', 'opacity' => 100, 'position' => 2],
                    ['color' => '#1b2a4a', 'opacity' => 100, 'position' => 19],
                    ['color' => '#c29b54', 'opacity' => 100, 'position' => 50],
                    ['color' => '#F03225', 'opacity' => 100, 'position' => 100],
                ],
            ],
            [
                'slug' => 'cta-gradient',
                'name' => 'CTA Gradient',
                'description' => 'Compact two-tone CTA sweep (header button, form submits).',
                'type' => 'linear', 'angle' => 135, 'stops' => [
                    ['color' => '#1b2a4a', 'opacity' => 100, 'position' => 0],
                    ['color' => '#c29b54', 'opacity' => 100, 'position' => 100],
                ],
            ],
            [
                'slug' => 'hero-gradient',
                'name' => 'Hero Gradient',
                'description' => 'Dark cinematic overlay for hero/video sections.',
                'type' => 'linear', 'angle' => 180, 'stops' => [
                    ['color' => '#0A0E1A', 'opacity' => 20, 'position' => 0],
                    ['color' => '#0A0E1A', 'opacity' => 55, 'position' => 60],
                    ['color' => '#0A0E1A', 'opacity' => 80, 'position' => 100],
                ],
            ],
            [
                'slug' => 'sunset-gradient',
                'name' => 'Sunset Gradient',
                'description' => 'Warm sunset sweep for feature cards and banners.',
                'type' => 'linear', 'angle' => 45, 'stops' => [
                    ['color' => '#c29b54', 'opacity' => 100, 'position' => 0],
                    ['color' => '#F03225', 'opacity' => 100, 'position' => 55],
                    ['color' => '#14203a', 'opacity' => 100, 'position' => 100],
                ],
            ],
            [
                'slug' => 'luxury-gradient',
                'name' => 'Luxury Gradient',
                'description' => 'Gold radial glow (category card backdrop tone).',
                'type' => 'radial', 'angle' => 0, 'position' => '50% 25%', 'stops' => [
                    ['color' => '#c29b54', 'opacity' => 100, 'position' => 0],
                    ['color' => '#c29b54', 'opacity' => 100, 'position' => 55],
                    ['color' => '#96631D', 'opacity' => 100, 'position' => 100],
                ],
            ],
            [
                'slug' => 'footer-gradient',
                'name' => 'Footer Gradient',
                'description' => 'Deep indigo fade for footer/section overlays.',
                'type' => 'linear', 'angle' => 180, 'stops' => [
                    ['color' => '#1b2a4a', 'opacity' => 92, 'position' => 0],
                    ['color' => '#101828', 'opacity' => 100, 'position' => 100],
                ],
            ],
        ];

        foreach ($gradients as $data) {
            Gradient::query()->firstOrCreate(['slug' => $data['slug']], $data + ['is_active' => true]);
        }

        $buttons = [
            [
                'slug' => 'primary-pill',
                'name' => 'Primary Pill',
                'description' => 'Design CTA (02 Hero.png): multi-stop brand gradient, rounded left edge, uppercase.',
                'config' => array_replace_recursive(ButtonStyle::defaults(), [
                    'typography' => ['size' => 15, 'transform' => 'uppercase', 'letter_spacing' => 1],
                    'padding' => [
                        'desktop' => ['top' => 18, 'right' => 38, 'bottom' => 18, 'left' => 38],
                        'tablet' => ['top' => 15, 'right' => 32, 'bottom' => 15, 'left' => 32],
                        'mobile' => ['top' => 13, 'right' => 26, 'bottom' => 13, 'left' => 26],
                    ],
                    'border' => ['radius' => ['tl' => '999px', 'tr' => '12px', 'br' => '12px', 'bl' => '999px']],
                    'background' => ['mode' => 'gradient', 'gradient_slug' => 'primary-gradient', 'text_color' => '#ffffff'],
                    'animation' => ['hover_effect' => 'lift', 'duration' => 250],
                    'shadows' => [['x' => 0, 'y' => 6, 'blur' => 18, 'spread' => 0, 'color' => '#F03225', 'opacity' => 25, 'inset' => false]],
                ]),
            ],
            [
                'slug' => 'outline-light',
                'name' => 'Outline Light',
                'description' => 'Design secondary CTA (02 Hero.png): white 1px outline, softly squared, uppercase.',
                'config' => array_replace_recursive(ButtonStyle::defaults(), [
                    'typography' => ['size' => 15, 'transform' => 'uppercase', 'letter_spacing' => 1],
                    'padding' => [
                        'desktop' => ['top' => 18, 'right' => 38, 'bottom' => 18, 'left' => 38],
                        'tablet' => ['top' => 15, 'right' => 32, 'bottom' => 15, 'left' => 32],
                        'mobile' => ['top' => 13, 'right' => 26, 'bottom' => 13, 'left' => 26],
                    ],
                    'border' => ['width' => 1, 'style' => 'solid', 'color' => '#ffffff', 'radius' => ['tl' => '10px', 'tr' => '10px', 'br' => '10px', 'bl' => '10px']],
                    'background' => ['mode' => 'transparent', 'gradient_slug' => null, 'text_color' => '#ffffff'],
                    'hover' => ['mode' => 'solid', 'color' => '#ffffff', 'text_color' => '#101828'],
                    'animation' => ['hover_effect' => 'scale', 'duration' => 250],
                ]),
            ],
        ];

        $buttons[] = [
            'slug' => 'secondary-solid',
            'name' => 'Secondary Solid',
            'description' => 'Solid indigo pill for secondary CTAs on light surfaces.',
            'config' => array_replace_recursive(ButtonStyle::defaults(), [
                'background' => ['mode' => 'solid', 'color' => '#1b2a4a', 'gradient_slug' => null, 'text_color' => '#ffffff'],
                'hover' => ['mode' => 'solid', 'color' => '#1b2a4a', 'text_color' => '#ffffff'],
                'animation' => ['hover_effect' => 'scale', 'duration' => 200],
            ]),
        ];
        $buttons[] = [
            'slug' => 'gradient-cta',
            'name' => 'Gradient CTA',
            'description' => 'Compact two-tone CTA sweep with ripple feedback.',
            'config' => array_replace_recursive(ButtonStyle::defaults(), [
                'background' => ['mode' => 'gradient', 'gradient_slug' => 'cta-gradient', 'text_color' => '#ffffff'],
                'animation' => ['hover_effect' => 'ripple', 'duration' => 400],
                'icon' => ['right' => 'fas fa-arrow-right', 'gap' => 8],
            ]),
        ];

        foreach ($buttons as $data) {
            ButtonStyle::query()->firstOrCreate(['slug' => $data['slug']], $data + ['is_active' => true]);
        }

        app(GradientService::class)->flushCache();
        app(ButtonStyleService::class)->flushCache();
    }
}
