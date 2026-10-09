<?php

declare(strict_types=1);

use App\Models\ButtonStyle;
use App\Models\Form;
use App\Models\Gradient;
use Illuminate\Database\Migrations\Migration;

/**
 * The design-system seed carried a previous client's look: a navy/gold/red
 * gradient set, a pill rounded on the left only with a red glow, and button
 * descriptions pointing at their design files. Rows are changed only while
 * they still hold exactly the old seeded values, so anything an admin edited
 * is left alone; down() reverses only rows that still hold the new values.
 *
 * The Dealer, Distributor and Career forms are switched off (hidden from
 * every form picker). The forms and their submissions stay.
 */
return new class extends Migration
{
    private const RETIRED_FORMS = ['distributor-form', 'dealer-form', 'career-form'];

    public function up(): void
    {
        $this->swap('old', 'new');
        Form::query()->whereIn('slug', self::RETIRED_FORMS)->update(['is_active' => false]);
        $this->flush();
    }

    public function down(): void
    {
        $this->swap('new', 'old');
        Form::query()->whereIn('slug', self::RETIRED_FORMS)->update(['is_active' => true]);
        $this->flush();
    }

    private function swap(string $from, string $to): void
    {
        foreach ($this->gradients() as $slug => $versions) {
            $row = Gradient::query()->where('slug', $slug)->first();

            if ($row && $row->name === $versions[$from]['name']
                && $row->description === $versions[$from]['description']
                && $row->stops == $versions[$from]['stops']) {
                $row->update($versions[$to]);
            }
        }

        foreach ($this->buttons() as $slug => $versions) {
            $row = ButtonStyle::query()->where('slug', $slug)->first();

            if (! $row || $row->description !== $versions[$from]['description']) {
                continue;
            }

            $config = (array) $row->config;
            $matches = collect($versions[$from]['config'])->every(fn ($value, $path) => data_get($config, $path) == $value);

            if ($matches) {
                foreach ($versions[$to]['config'] as $path => $value) {
                    data_set($config, $path, $value);
                }
                $row->update(['description' => $versions[$to]['description'], 'config' => $config]);
            }
        }
    }

    /** @return array<string, array{old: array, new: array}> */
    private function gradients(): array
    {
        $stop = fn (string $color, int $position, int $opacity = 100) => ['color' => $color, 'opacity' => $opacity, 'position' => $position];

        return [
            'primary-gradient' => [
                'old' => ['name' => 'Primary Gradient', 'description' => 'Brand magenta → orange sweep used on primary CTAs (design reference).',
                    'stops' => [$stop('#14203a', 2), $stop('#1b2a4a', 19), $stop('#c29b54', 50), $stop('#F03225', 100)]],
                'new' => ['name' => 'Primary Gradient', 'description' => 'Dark-to-slate sweep for primary buttons.',
                    'stops' => [$stop('#111827', 0), $stop('#374151', 100)]],
            ],
            'cta-gradient' => [
                'old' => ['name' => 'CTA Gradient', 'description' => 'Compact two-tone CTA sweep (header button, form submits).',
                    'stops' => [$stop('#1b2a4a', 0), $stop('#c29b54', 100)]],
                'new' => ['name' => 'CTA Gradient', 'description' => 'Two-tone sweep for header buttons and form submits.',
                    'stops' => [$stop('#1f2937', 0), $stop('#4b5563', 100)]],
            ],
            'sunset-gradient' => [
                'old' => ['name' => 'Sunset Gradient', 'description' => 'Warm sunset sweep for feature cards and banners.',
                    'stops' => [$stop('#c29b54', 0), $stop('#F03225', 55), $stop('#14203a', 100)]],
                'new' => ['name' => 'Sunset Gradient', 'description' => 'Warm sweep for feature cards and banners.',
                    'stops' => [$stop('#f59e0b', 0), $stop('#ef4444', 100)]],
            ],
            'luxury-gradient' => [
                'old' => ['name' => 'Luxury Gradient', 'description' => 'Gold radial glow (category card backdrop tone).',
                    'stops' => [$stop('#c29b54', 0), $stop('#c29b54', 55), $stop('#96631D', 100)]],
                'new' => ['name' => 'Soft Radial', 'description' => 'Soft radial glow for card backdrops.',
                    'stops' => [$stop('#f9fafb', 0), $stop('#e5e7eb', 100)]],
            ],
            'footer-gradient' => [
                'old' => ['name' => 'Footer Gradient', 'description' => 'Deep indigo fade for footer/section overlays.',
                    'stops' => [$stop('#1b2a4a', 0, 92), $stop('#101828', 100)]],
                'new' => ['name' => 'Footer Gradient', 'description' => 'Deep fade for footer and section overlays.',
                    'stops' => [$stop('#1f2937', 0, 92), $stop('#111827', 100)]],
            ],
        ];
    }

    /** Config paths compared (old) and written (new) per button. */
    private function buttons(): array
    {
        return [
            'primary-pill' => [
                'old' => ['description' => 'Design CTA (02 Hero.png): multi-stop brand gradient, rounded left edge, uppercase.',
                    'config' => ['border.radius.tr' => '12px', 'border.radius.br' => '12px',
                        'shadows' => [['x' => 0, 'y' => 6, 'blur' => 18, 'spread' => 0, 'color' => '#F03225', 'opacity' => 25, 'inset' => false]]]],
                'new' => ['description' => 'Gradient pill for primary calls to action, uppercase.',
                    'config' => ['border.radius.tr' => '999px', 'border.radius.br' => '999px', 'shadows' => []]],
            ],
            'outline-light' => [
                'old' => ['description' => 'Design secondary CTA (02 Hero.png): white 1px outline, softly squared, uppercase.', 'config' => []],
                'new' => ['description' => 'White 1px outline for dark backgrounds, softly squared, uppercase.', 'config' => []],
            ],
            'secondary-solid' => [
                'old' => ['description' => 'Solid indigo pill for secondary CTAs on light surfaces.',
                    'config' => ['background.color' => '#1b2a4a', 'hover.color' => '#1b2a4a']],
                'new' => ['description' => 'Solid pill for secondary calls to action on light backgrounds.',
                    'config' => ['background.color' => '#111827', 'hover.color' => '#111827']],
            ],
        ];
    }

    private function flush(): void
    {
        app(\App\Services\GradientService::class)->flushCache();
        app(\App\Services\ButtonStyleService::class)->flushCache();
        app(\App\Services\ThemeService::class)->flushCache();
    }
};
