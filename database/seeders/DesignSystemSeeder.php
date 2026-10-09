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
                'description' => 'Dark-to-slate sweep for primary buttons.',
                'type' => 'linear', 'angle' => 90, 'stops' => [
                    ['color' => '#111827', 'opacity' => 100, 'position' => 0],
                    ['color' => '#374151', 'opacity' => 100, 'position' => 100],
                ],
            ],
            [
                'slug' => 'cta-gradient',
                'name' => 'CTA Gradient',
                'description' => 'Two-tone sweep for header buttons and form submits.',
                'type' => 'linear', 'angle' => 135, 'stops' => [
                    ['color' => '#1f2937', 'opacity' => 100, 'position' => 0],
                    ['color' => '#4b5563', 'opacity' => 100, 'position' => 100],
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
                'description' => 'Warm sweep for feature cards and banners.',
                'type' => 'linear', 'angle' => 45, 'stops' => [
                    ['color' => '#f59e0b', 'opacity' => 100, 'position' => 0],
                    ['color' => '#ef4444', 'opacity' => 100, 'position' => 100],
                ],
            ],
            [
                'slug' => 'luxury-gradient',
                'name' => 'Soft Radial',
                'description' => 'Soft radial glow for card backdrops.',
                'type' => 'radial', 'angle' => 0, 'position' => '50% 25%', 'stops' => [
                    ['color' => '#f9fafb', 'opacity' => 100, 'position' => 0],
                    ['color' => '#e5e7eb', 'opacity' => 100, 'position' => 100],
                ],
            ],
            [
                'slug' => 'footer-gradient',
                'name' => 'Footer Gradient',
                'description' => 'Deep fade for footer and section overlays.',
                'type' => 'linear', 'angle' => 180, 'stops' => [
                    ['color' => '#1f2937', 'opacity' => 92, 'position' => 0],
                    ['color' => '#111827', 'opacity' => 100, 'position' => 100],
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
                'description' => 'Gradient pill for primary calls to action, uppercase.',
                'config' => array_replace_recursive(ButtonStyle::defaults(), [
                    'typography' => ['size' => 15, 'transform' => 'uppercase', 'letter_spacing' => 1],
                    'padding' => [
                        'desktop' => ['top' => 18, 'right' => 38, 'bottom' => 18, 'left' => 38],
                        'tablet' => ['top' => 15, 'right' => 32, 'bottom' => 15, 'left' => 32],
                        'mobile' => ['top' => 13, 'right' => 26, 'bottom' => 13, 'left' => 26],
                    ],
                    'border' => ['radius' => ['tl' => '999px', 'tr' => '999px', 'br' => '999px', 'bl' => '999px']],
                    'background' => ['mode' => 'gradient', 'gradient_slug' => 'primary-gradient', 'text_color' => '#ffffff'],
                    'animation' => ['hover_effect' => 'lift', 'duration' => 250],
                ]),
            ],
            [
                'slug' => 'outline-light',
                'name' => 'Outline Light',
                'description' => 'White 1px outline for dark backgrounds, softly squared, uppercase.',
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
            'description' => 'Solid pill for secondary calls to action on light backgrounds.',
            'config' => array_replace_recursive(ButtonStyle::defaults(), [
                'background' => ['mode' => 'solid', 'color' => '#111827', 'gradient_slug' => null, 'text_color' => '#ffffff'],
                'hover' => ['mode' => 'solid', 'color' => '#111827', 'text_color' => '#ffffff'],
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
