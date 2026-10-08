<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ButtonStyle;
use App\Models\Gradient;
use App\Services\SettingsService;
use App\Services\ThemeService;
use Illuminate\Contracts\View\View;

/**
 * Brand: one screen for the handful of choices that set the look of the whole
 * site — logo, colours, fonts, corners, buttons.
 *
 * It does not own any of them. Logo lives in Settings, the rest in the Theme
 * Customizer, and each still has its full screen for the fiddly work. This is
 * the front door: the first thing a new user should open, showing what is set
 * and what is still missing, with one link per job.
 */
class BrandController extends Controller
{
    public function __construct(
        protected ThemeService $theme,
        protected SettingsService $settings,
    ) {
        $this->middleware('can:theme.manage');
    }

    public function index(): View
    {
        return view('admin.brand.index', [
            'logo' => setting_image('branding.logo_dark') ?: setting_image('branding.logo_light'),
            'favicon' => setting_image('branding.favicon'),
            'companyName' => setting('business.company_name', config('app.name')),
            'colors' => $this->colors(),
            'fonts' => $this->fonts(),
            'radius' => (int) theme('radius.buttons', 8),
            'buttonStyles' => ButtonStyle::query()->orderBy('name')->limit(6)->get(),
            'gradients' => Gradient::query()->orderBy('name')->limit(6)->get(),
        ]);
    }

    /**
     * The colours worth showing on a summary: the ones that change how the
     * site feels. Success/warning/danger are status colours nobody picks for
     * brand reasons, so they stay on the Theme Customizer screen.
     *
     * @return array<string, array{label: string, value: string}>
     */
    private function colors(): array
    {
        $wanted = ['primary', 'secondary', 'accent', 'dark'];
        $fields = (array) config('theme.colors.fields', []);
        $colors = [];

        foreach ($wanted as $key) {
            if (! isset($fields[$key])) {
                continue;
            }

            $colors[$key] = [
                'label' => $fields[$key]['label'] ?? ucfirst($key),
                'value' => (string) theme("colors.{$key}", $fields[$key]['default'] ?? '#000000'),
            ];
        }

        return $colors;
    }

    /**
     * Distinct font families in use, with the roles each one covers — the
     * question a user is actually asking is "what fonts is my site using?",
     * not "what is the family for the sub-heading role?".
     *
     * @return array<string, list<string>>
     */
    private function fonts(): array
    {
        $roles = (array) config('theme.typography.roles', []);
        $families = [];

        foreach ($roles as $role => $meta) {
            $family = (string) theme("typography.{$role}.family", $meta['default_family'] ?? '');

            if ($family === '') {
                continue;
            }

            $families[$family][] = $meta['label'] ?? $role;
        }

        return $families;
    }
}
