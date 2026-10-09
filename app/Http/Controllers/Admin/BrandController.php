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
            'tokens' => $this->colorTokens(),
            'fonts' => $this->fonts(),
            'radius' => (int) theme('radius.buttons', 8),
            'buttonStyles' => ButtonStyle::query()->orderBy('name')->limit(6)->get(),
            'gradients' => Gradient::query()->orderBy('name')->limit(6)->get(),
        ]);
    }

    /**
     * Design tokens are the CSS variables in Theme Customizer → Custom code
     * (one "--name: value" per line). Brand edits the colour ones in place,
     * so both screens edit the same text.
     *
     * @return array<string, string> --name => #hex
     */
    private function colorTokens(): array
    {
        // 3- or 6-digit hex only: a colour picker can't hold alpha, so tokens
        // with transparency stay with Theme Customizer's text field.
        preg_match_all('/^\s*(--[a-z0-9_-]+)\s*:\s*(#(?:[0-9a-f]{3}|[0-9a-f]{6}))\s*;?\s*$/im', (string) theme('custom_code.css_variables', ''), $matches, PREG_SET_ORDER);

        return collect($matches)->mapWithKeys(fn ($m) => [$m[1] => $m[2]])->all();
    }

    /** Save the colour tokens edited on the Brand screen back into the same text. */
    public function updateTokens(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'tokens' => ['required', 'array', 'max:200'],
            'tokens.*' => ['required', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
        ]);

        $text = (string) theme('custom_code.css_variables', '');
        foreach ($data['tokens'] as $name => $value) {
            $text = preg_replace('/^(\s*'.preg_quote((string) $name, '/').'\s*:\s*)#[0-9a-f]{3,8}/im', '${1}'.$value, $text);
        }

        $this->theme->updateGroup('custom_code', ['css_variables' => $text]);

        return redirect()->route('admin.brand.index')->with('success', 'Design colours saved.');
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
            // Same key Theme Customizer writes ({role}_family): one source of truth.
            $family = (string) theme("typography.{$role}_family", $meta['default_family'] ?? '');

            if ($family === '') {
                continue;
            }

            $families[$family][] = $meta['label'] ?? $role;
        }

        return $families;
    }
}
