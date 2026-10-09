<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MotionPreset;
use App\Models\Section;
use App\Support\SectionMotion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Motion Library (Design → Motion Library): named motion that section
 * Motion tabs point at. Same shape as the Gradient Library.
 */
class MotionPresetController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:motion.manage');
    }

    public function index(): View
    {
        return view('admin.motion-presets.index', ['presets' => MotionPreset::query()->orderBy('kind')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.motion-presets.form', ['preset' => new MotionPreset([
            'kind' => 'anime', 'trigger' => 'enter', 'duration' => 700, 'delay' => 0, 'stagger' => 0, 'ease' => 'outQuad',
            'keyframes' => ['opacity' => [0, 1], 'translateY' => [32, 0]],
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $preset = MotionPreset::create($this->validated($request));

        return redirect()->route('admin.motion-presets.edit', $preset)->with('success', 'Preset created.');
    }

    public function edit(MotionPreset $motionPreset): View
    {
        return view('admin.motion-presets.form', ['preset' => $motionPreset]);
    }

    public function update(Request $request, MotionPreset $motionPreset): RedirectResponse
    {
        $motionPreset->update($this->validated($request, $motionPreset));

        return redirect()->route('admin.motion-presets.edit', $motionPreset)->with('success', 'Preset saved.');
    }

    public function destroy(MotionPreset $motionPreset): RedirectResponse
    {
        abort_if($motionPreset->is_system, 403, 'Built-in presets cannot be deleted. Duplicate one to make your own version.');
        $motionPreset->delete();

        return redirect()->route('admin.motion-presets.index')->with('success', 'Preset deleted. Tracks that used it stop moving until they get another.');
    }

    public function duplicate(MotionPreset $motionPreset): RedirectResponse
    {
        $copy = $motionPreset->replicate(['is_system']);
        $copy->name = Str::limit($motionPreset->name.' (copy)', 100, '');
        $copy->slug = $this->uniqueSlug($motionPreset->slug.'-copy');
        $copy->is_system = false;
        $copy->save();

        return redirect()->route('admin.motion-presets.edit', $copy)->with('success', 'Copy created.');
    }

    public function export(): StreamedResponse
    {
        // Everything but ids, dates and the built-in flag.
        $payload = ['motion_presets' => MotionPreset::query()->orderBy('slug')->get()
            ->map(fn (MotionPreset $p) => $p->only(['name', 'slug', 'kind', 'trigger', 'keyframes', 'params', 'duration', 'delay', 'ease', 'stagger', 'description']))->all()];

        return response()->streamDownload(
            fn () => print (json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            'motion-presets-'.now()->format('Y-m-d-His').'.json',
            ['Content-Type' => 'application/json'],
        );
    }

    /**
     * Import a file from export(): each preset is checked exactly like the
     * form. New slugs are added, your own presets with the same slug are
     * updated, built-in ones are never overwritten.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:json,txt', 'max:1024']]);
        $decoded = json_decode((string) file_get_contents($request->file('file')->getRealPath()), true);
        $rows = is_array($decoded['motion_presets'] ?? null) ? $decoded['motion_presets'] : null;

        if ($rows === null || count($rows) > 500) {
            throw ValidationException::withMessages(['file' => 'That file is not a Motion Library export.']);
        }

        $added = $updated = $skipped = 0;
        foreach ($rows as $i => $row) {
            $existing = is_array($row) ? MotionPreset::query()->where('slug', (string) ($row['slug'] ?? ''))->first() : null;

            if ($existing?->is_system) {
                $skipped++;
                continue;
            }

            $input = is_array($row) ? $row : [];
            $input['keyframes'] = json_encode($input['keyframes'] ?? null);
            $input['params'] = isset($input['params']) ? json_encode($input['params']) : null;
            $validator = validator($input, $this->rules($existing));

            if ($validator->fails()) {
                throw ValidationException::withMessages(['file' => 'Preset '.($i + 1).': '.$validator->errors()->first()]);
            }

            $data = $this->clean($validator->validated(), $existing, 'Preset '.($i + 1).': ');
            $existing ? $existing->update($data) : MotionPreset::create($data);
            $existing ? $updated++ : $added++;
        }

        return redirect()->route('admin.motion-presets.index')
            ->with('success', "Imported: {$added} added, {$updated} updated".($skipped ? ", {$skipped} built-in kept as they are" : '').'.');
    }

    /**
     * A small page running the real motion engine on a sample, for the
     * preset form's preview (scroll slider for scroll presets, replay for the rest).
     */
    public function preview(MotionPreset $motionPreset): View
    {
        $section = (new Section())->forceFill(['id' => 0, 'type' => 'motion-preview', 'style' => ['motion' => ['desktop' => [
            'stage' => ['pin' => true, 'length' => 1000],
            'tracks' => ['demo' => ['part' => 'demo', 'preset' => $motionPreset->slug]
                + ($motionPreset->trigger === 'scrub' ? ['range' => [0, 1000]] : [])
                + ($motionPreset->trigger === 'state' ? ['trigger' => 'load'] : [])
                + ($motionPreset->stagger > 0 && ! isset($motionPreset->keyframes['$split']) ? ['children' => true] : [])],
        ]]]]);

        return view('admin.motion-presets.preview', ['preset' => $motionPreset, 'motion' => SectionMotion::render($section)]);
    }

    /* ── internals ────────────────────────────────────────────────────── */

    private function rules(?MotionPreset $preset): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/', Rule::unique('motion_presets', 'slug')->ignore($preset?->id)],
            'kind' => ['required', Rule::in(array_keys(MotionPreset::KINDS))],
            'trigger' => ['required', Rule::in(array_keys(SectionMotion::TRIGGERS))],
            'keyframes' => ['required', 'string', 'max:20000'],
            'params' => ['nullable', 'string', 'max:5000'],
            'duration' => ['required', 'integer', 'between:0,60000'],
            'delay' => ['nullable', 'integer', 'between:0,60000'],
            'stagger' => ['nullable', 'integer', 'between:0,10000'],
            'ease' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function validated(Request $request, ?MotionPreset $preset = null): array
    {
        return $this->clean($request->validate($this->rules($preset)), $preset);
    }

    /** JSON fields decoded and checked; the same checks the engine's sanitiser applies. */
    private function clean(array $data, ?MotionPreset $preset, string $prefix = ''): array
    {
        $css = $data['kind'] === 'css';
        $keyframes = $css ? SectionMotion::cssKeyframes($data['keyframes']) : SectionMotion::keyframes($data['keyframes']);
        $params = json_decode((string) ($data['params'] ?? ''), true);
        $field = $prefix === '' ? fn ($key) => $key : fn () => 'file';

        if ($keyframes === null) {
            throw ValidationException::withMessages([$field('keyframes') => $prefix.($css
                ? 'Keyframes must be JSON like {"0%": {"opacity": "0"}, "100%": {"opacity": "1"}}, with no ; { } < > or url().'
                : 'Keyframes must be JSON like {"opacity": [0, 1], "translateY": [40, 0]}, with no ; { } < > or url().')]);
        }
        if ($css && $data['trigger'] === 'scrub') {
            throw ValidationException::withMessages([$field('trigger') => $prefix.'CSS keyframes run on time, not on scroll: use Anime.js keyframes for a scroll preset.']);
        }
        if (! SectionMotion::validEase($data['ease'], $data['kind'])) {
            throw ValidationException::withMessages([$field('ease') => $prefix.($css ? 'Use a CSS timing function, e.g. ease-in-out, linear, steps(4) or cubic-bezier(.2,.8,.2,1).' : 'Use an Anime.js ease, e.g. outCubic, inOutQuad, linear, steps(4) or spring(80,10).')]);
        }
        if (($data['params'] ?? '') !== '' && ! is_array($params)) {
            throw ValidationException::withMessages([$field('params') => $prefix.'Settings must be JSON like {"distance": 40}.']);
        }

        $data['keyframes'] = $keyframes;
        $data['params'] = is_array($params) ? SectionMotion::params($params) : null;
        $data['delay'] = (int) ($data['delay'] ?? 0);
        $data['stagger'] = (int) ($data['stagger'] ?? 0);
        $data['slug'] = ($data['slug'] ?? null) ?: ($preset?->slug ?? $this->uniqueSlug(Str::slug($data['name']) ?: 'motion'));

        return $data;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        for ($i = 2; MotionPreset::query()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
