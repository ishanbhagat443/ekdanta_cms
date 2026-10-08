<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gradient;
use App\Repositories\Contracts\GradientRepositoryInterface;
use App\Services\GradientService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradientController extends Controller
{
    public function __construct(
        protected GradientRepositoryInterface $gradients,
        protected GradientService $service,
    ) {
        $this->middleware('can:theme.manage');
    }

    public function index(): View
    {
        return view('admin.gradients.index', ['gradients' => $this->gradients->all()]);
    }

    public function create(): View
    {
        return view('admin.gradients.form', ['gradient' => new Gradient([
            'type' => 'linear',
            'angle' => 90,
            'is_active' => true,
            'stops' => [
                ['color' => '#1b2a4a', 'opacity' => 100, 'position' => 0],
                ['color' => '#c29b54', 'opacity' => 100, 'position' => 100],
            ],
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->service->create($this->validated($request));

        return redirect()->route('admin.gradients.index')->with('success', 'Gradient created.');
    }

    public function edit(Gradient $gradient): View
    {
        return view('admin.gradients.form', ['gradient' => $gradient]);
    }

    public function update(Request $request, Gradient $gradient): RedirectResponse
    {
        $this->service->update($gradient, $this->validated($request, $gradient));

        return redirect()->route('admin.gradients.index')->with('success', 'Gradient updated.');
    }

    public function destroy(Gradient $gradient): RedirectResponse
    {
        $this->service->delete($gradient);

        return redirect()->route('admin.gradients.index')->with('success', 'Gradient deleted.');
    }

    /** Unlimited stops; each stop = color + opacity % + position %. */
    private function validated(Request $request, ?Gradient $gradient = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', Rule::unique('gradients', 'slug')->ignore($gradient?->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'in:linear,radial,conic'],
            'angle' => ['nullable', 'integer', 'between:0,360'],
            'position' => ['nullable', 'string', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
            'stops' => ['required', 'array', 'min:2'],
            'stops.*.color' => ['required', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'stops.*.opacity' => ['required', 'numeric', 'between:0,100'],
            'stops.*.position' => ['required', 'numeric', 'between:0,100'],
        ]);

        $data['angle'] = $data['angle'] ?? 90;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['slug'] = ($data['slug'] ?? null) ?: null;
        $data['stops'] = array_values($data['stops']);

        if ($data['slug'] === null && $gradient !== null) {
            unset($data['slug']); // keep the existing slug on update
        }

        return $data;
    }
}
