<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ButtonStyle;
use App\Repositories\Contracts\ButtonStyleRepositoryInterface;
use App\Services\ButtonStyleService;
use App\Services\GradientService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ButtonStyleController extends Controller
{
    public function __construct(
        protected ButtonStyleRepositoryInterface $styles,
        protected ButtonStyleService $service,
        protected GradientService $gradients,
    ) {
        $this->middleware('can:theme.manage');
    }

    public function index(): View
    {
        return view('admin.button-styles.index', [
            'styles' => $this->styles->all(),
            'service' => $this->service,
        ]);
    }

    public function create(): View
    {
        return $this->form(new ButtonStyle([
            'is_active' => true,
            'config' => ButtonStyle::defaults(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->service->create($this->validated($request));

        return redirect()->route('admin.button-styles.index')->with('success', 'Button style created.');
    }

    public function edit(ButtonStyle $buttonStyle): View
    {
        return $this->form($buttonStyle);
    }

    public function update(Request $request, ButtonStyle $buttonStyle): RedirectResponse
    {
        $this->service->update($buttonStyle, $this->validated($request, $buttonStyle));

        return redirect()->route('admin.button-styles.index')->with('success', 'Button style updated.');
    }

    public function destroy(ButtonStyle $buttonStyle): RedirectResponse
    {
        $this->service->delete($buttonStyle);

        return redirect()->route('admin.button-styles.index')->with('success', 'Button style deleted.');
    }

    public function duplicate(ButtonStyle $buttonStyle): RedirectResponse
    {
        $this->service->create([
            'name' => $buttonStyle->name.' (copy)',
            'description' => $buttonStyle->description,
            'is_active' => false,
            'config' => $buttonStyle->config,
        ]);

        return redirect()->route('admin.button-styles.index')->with('success', 'Button style duplicated.');
    }

    private function form(ButtonStyle $style): View
    {
        return view('admin.button-styles.form', [
            'style' => $style,
            'gradients' => $this->gradients->active(),
            'fontRoles' => array_keys(config('theme.typography.roles')),
            'service' => $this->service,
        ]);
    }

    private function validated(Request $request, ?ButtonStyle $style = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', Rule::unique('button_styles', 'slug')->ignore($style?->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],

            'config' => ['required', 'array'],
            'config.typography.family' => ['nullable', 'string', 'max:120'],
            'config.typography.size' => ['nullable', 'numeric', 'between:8,80'],
            'config.typography.weight' => ['nullable', 'in:300,400,500,600,700,800'],
            'config.typography.letter_spacing' => ['nullable', 'numeric', 'between:-5,20'],
            'config.typography.transform' => ['nullable', 'in:none,uppercase,capitalize,lowercase'],
            'config.typography.line_height' => ['nullable', 'numeric', 'between:0.5,4'],

            'config.padding' => ['nullable', 'array'],
            'config.padding.*.top' => ['nullable', 'numeric', 'between:0,200'],
            'config.padding.*.right' => ['nullable', 'numeric', 'between:0,200'],
            'config.padding.*.bottom' => ['nullable', 'numeric', 'between:0,200'],
            'config.padding.*.left' => ['nullable', 'numeric', 'between:0,200'],

            'config.border.width' => ['nullable', 'numeric', 'between:0,20'],
            'config.border.style' => ['nullable', 'in:solid,dashed,dotted,double,none'],
            'config.border.color' => ['nullable', 'string', 'max:30'],
            // Free-form per-corner values: 0px / 5px / 20px / 50% / 999px …
            'config.border.radius.tl' => ['nullable', 'regex:/^\d+(\.\d+)?(px|%|rem|em)?$/'],
            'config.border.radius.tr' => ['nullable', 'regex:/^\d+(\.\d+)?(px|%|rem|em)?$/'],
            'config.border.radius.br' => ['nullable', 'regex:/^\d+(\.\d+)?(px|%|rem|em)?$/'],
            'config.border.radius.bl' => ['nullable', 'regex:/^\d+(\.\d+)?(px|%|rem|em)?$/'],

            'config.background.mode' => ['required', 'in:solid,gradient,image,video,transparent'],
            'config.background.color' => ['nullable', 'string', 'max:30'],
            'config.background.gradient_slug' => ['nullable', 'string', 'exists:gradients,slug'],
            'config.background.media_id' => ['nullable', 'integer', 'exists:media,id'],
            'config.background.video_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'config.background.text_color' => ['nullable', 'string', 'max:30'],

            'config.hover.mode' => ['nullable', 'in:solid,gradient,image,transparent'],
            'config.hover.color' => ['nullable', 'string', 'max:30'],
            'config.hover.gradient_slug' => ['nullable', 'string', 'exists:gradients,slug'],
            'config.hover.text_color' => ['nullable', 'string', 'max:30'],
            'config.hover.border.width' => ['nullable', 'numeric', 'between:0,20'],
            'config.hover.border.style' => ['nullable', 'in:solid,dashed,dotted,double,none'],
            'config.hover.border.color' => ['nullable', 'string', 'max:30'],
            'config.hover.radius.tl' => ['nullable', 'regex:/^\d+(\.\d+)?(px|%|rem|em)?$/'],
            'config.hover.radius.tr' => ['nullable', 'regex:/^\d+(\.\d+)?(px|%|rem|em)?$/'],
            'config.hover.radius.br' => ['nullable', 'regex:/^\d+(\.\d+)?(px|%|rem|em)?$/'],
            'config.hover.radius.bl' => ['nullable', 'regex:/^\d+(\.\d+)?(px|%|rem|em)?$/'],
            'config.hover.shadows' => ['nullable', 'array'],
            'config.hover.shadows.*.x' => ['nullable', 'numeric', 'between:-100,100'],
            'config.hover.shadows.*.y' => ['nullable', 'numeric', 'between:-100,100'],
            'config.hover.shadows.*.blur' => ['nullable', 'numeric', 'between:0,200'],
            'config.hover.shadows.*.spread' => ['nullable', 'numeric', 'between:-100,100'],
            'config.hover.shadows.*.color' => ['nullable', 'string', 'max:30'],
            'config.hover.shadows.*.opacity' => ['nullable', 'numeric', 'between:0,100'],
            'config.hover.shadows.*.inset' => ['nullable', 'boolean'],

            'config.animation.hover_effect' => ['nullable', 'in:none,scale,ripple,glow,lift,slide,pulse'],
            'config.animation.duration' => ['nullable', 'integer', 'between:50,3000'],

            'config.icon.left' => ['nullable', 'string', 'max:60'],
            'config.icon.right' => ['nullable', 'string', 'max:60'],
            'config.icon.gap' => ['nullable', 'numeric', 'between:0,60'],

            'config.shadows' => ['nullable', 'array'],
            'config.shadows.*.x' => ['nullable', 'numeric', 'between:-100,100'],
            'config.shadows.*.y' => ['nullable', 'numeric', 'between:-100,100'],
            'config.shadows.*.blur' => ['nullable', 'numeric', 'between:0,200'],
            'config.shadows.*.spread' => ['nullable', 'numeric', 'between:-100,100'],
            'config.shadows.*.color' => ['nullable', 'string', 'max:30'],
            'config.shadows.*.opacity' => ['nullable', 'numeric', 'between:0,100'],
            'config.shadows.*.inset' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['slug'] = ($data['slug'] ?? null) ?: null;

        if ($data['slug'] === null && $style !== null) {
            unset($data['slug']);
        }

        foreach (['shadows', 'hover.shadows'] as $listKey) {
            $list = data_get($data['config'], $listKey);
            if (is_array($list)) {
                data_set($data['config'], $listKey, array_values(array_filter($list, fn ($s) => ! empty($s['color']))));
            }
        }

        return $data;
    }
}
