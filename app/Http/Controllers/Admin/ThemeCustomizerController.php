<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateThemeRequest;
use App\Models\ThemeFont;
use App\Services\ThemeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ThemeCustomizerController extends Controller
{
    public function __construct(protected ThemeService $theme)
    {
        $this->middleware('can:theme.manage');
    }

    /** Single-page tabbed customizer — every group renders in one screen for live preview. */
    public function index(): View
    {
        $groups = config('theme');
        $values = [];

        foreach (array_keys($groups) as $group) {
            $values[$group] = $group === 'typography'
                ? $this->typographyValues()
                : $this->theme->group($group);
        }

        return view('admin.theme.index', [
            'groups' => $groups,
            'values' => $values,
            'fonts' => ThemeFont::query()->orderBy('name')->get(),
            'presets' => \App\Models\ThemePreset::query()->latest('id')->get(),
            'previewUrl' => route('front.home'),
        ]);
    }

    public function update(UpdateThemeRequest $request, string $group): RedirectResponse
    {
        abort_if(config("theme.{$group}") === null, 404);
        abort_if($group === 'typography', 404); // handled by updateTypography()

        $data = $request->validated();

        // Fields flagged 'developer' (the site-wide scripts) need Developer
        // Mode. Dropped, not nulled: updateGroup() leaves absent keys alone.
        if (! \Illuminate\Support\Facades\Gate::allows('developer.mode')) {
            $data = array_diff_key($data, array_filter((array) config("theme.{$group}.fields"), fn ($f) => ! empty($f['developer'])));
        }

        $this->theme->updateGroup($group, $data);

        return redirect()
            ->route('admin.theme.index')
            ->with('success', config("theme.{$group}.label").' saved.');
    }

    public function updateTypography(Request $request): RedirectResponse
    {
        $data = $request->validate($this->typographyRules());

        $this->theme->updateTypography($data['roles'] ?? []);

        return redirect()->route('admin.theme.index')->with('success', 'Typography saved.');
    }

    /* ── Import / Export / Reset / Backup ─────────────────────────────── */

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $payload = [
            'exported_at' => now()->toIso8601String(),
            'app' => config('gsm.name'),
            'settings' => $this->theme->exportPayload(),
        ];

        return response()->streamDownload(
            fn () => print (json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
            'theme-export-'.now()->format('Y-m-d-His').'.json',
            ['Content-Type' => 'application/json'],
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:json', 'max:2048']]);

        $decoded = json_decode((string) file_get_contents($request->file('file')->getRealPath()), true);

        if (! is_array($decoded) || ! isset($decoded['settings']) || ! is_array($decoded['settings'])) {
            throw ValidationException::withMessages(['file' => 'That file is not a valid theme export.']);
        }

        $this->theme->importPayload($decoded['settings']);

        return redirect()->route('admin.theme.index')->with('success', 'Theme imported successfully.');
    }

    public function reset(): RedirectResponse
    {
        $this->theme->resetToDefault();

        return redirect()->route('admin.theme.index')->with('success', 'Theme reset to defaults.');
    }

    public function backup(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150']]);

        $this->theme->savePreset($data['name']);

        return redirect()->route('admin.theme.index')->with('success', 'Backup "'.$data['name'].'" saved.');
    }

    public function restore(\App\Models\ThemePreset $preset): RedirectResponse
    {
        $this->theme->restorePreset($preset);

        return redirect()->route('admin.theme.index')->with('success', 'Restored "'.$preset->name.'".');
    }

    public function destroyBackup(\App\Models\ThemePreset $preset): RedirectResponse
    {
        abort_if($preset->is_system, 403, 'The factory-default snapshot cannot be deleted.');
        $preset->delete();

        return redirect()->route('admin.theme.index')->with('success', 'Backup deleted.');
    }

    /* ── Local font upload ─────────────────────────────────────────────── */

    public function storeFont(Request $request, \App\Services\ThemeFontService $fonts): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'font_weight' => ['nullable', 'in:300,400,500,600,700,800'],
            'font_style' => ['nullable', 'in:normal,italic'],
            'woff2' => ['nullable', 'file', 'mimes:woff2', 'max:2048'],
            'woff' => ['nullable', 'file', 'mimes:woff', 'max:2048'],
            'ttf' => ['nullable', 'file', 'mimes:ttf', 'max:4096'],
        ]);

        if (! $request->hasFile('woff2') && ! $request->hasFile('woff') && ! $request->hasFile('ttf')) {
            throw ValidationException::withMessages(['woff2' => 'Upload at least one font file (WOFF2, WOFF, or TTF).']);
        }

        $fonts->upload($data + [
            'woff2' => $request->file('woff2'),
            'woff' => $request->file('woff'),
            'ttf' => $request->file('ttf'),
        ]);

        return redirect()->route('admin.theme.index')->with('success', 'Font uploaded.');
    }

    public function destroyFont(ThemeFont $font, \App\Services\ThemeFontService $fonts): RedirectResponse
    {
        $fonts->delete($font);

        return redirect()->route('admin.theme.index')->with('success', 'Font deleted.');
    }

    /* ── internals ─────────────────────────────────────────────────────── */

    private function typographyValues(): array
    {
        $values = [];

        foreach (array_keys(config('theme.typography.roles')) as $role) {
            foreach (['family', 'weight', 'transform', 'letter_spacing', 'line_height', 'size_desktop', 'size_tablet', 'size_mobile'] as $prop) {
                $values[$role][$prop] = $this->theme->get("typography.{$role}_{$prop}");
            }
        }

        return $values;
    }

    private function typographyRules(): array
    {
        $rules = [];
        $weights = implode(',', array_keys(config('theme.typography.weights')));
        $transforms = implode(',', array_keys(config('theme.typography.transforms')));

        foreach (array_keys(config('theme.typography.roles')) as $role) {
            // A Google Font name, an uploaded font (local:slug) or a stack.
            $rules["roles.{$role}.family"] = ['nullable', 'string', 'max:150', 'regex:/^(local:[a-z0-9-]+|[\pL\pN ,\'"_-]+)$/u'];
            $rules["roles.{$role}.weight"] = ['nullable', Rule::in(explode(',', $weights))];
            $rules["roles.{$role}.transform"] = ['nullable', Rule::in(explode(',', $transforms))];
            $rules["roles.{$role}.letter_spacing"] = ['nullable', 'numeric', 'between:-5,20'];
            $rules["roles.{$role}.line_height"] = ['nullable', 'numeric', 'between:0.8,3'];
            $rules["roles.{$role}.size_desktop"] = ['nullable', 'numeric', 'between:8,120'];
            $rules["roles.{$role}.size_tablet"] = ['nullable', 'numeric', 'between:8,120'];
            $rules["roles.{$role}.size_mobile"] = ['nullable', 'numeric', 'between:8,120'];
        }

        return $rules;
    }
}
