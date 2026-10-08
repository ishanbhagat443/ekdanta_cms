<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RedirectController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:seo.manage');
    }

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        return view('admin.redirects.index', [
            'redirects' => Redirect::query()
                ->when($search, fn ($q) => $q->where(fn ($w) => $w
                    ->where('from_path', 'like', "%{$search}%")
                    ->orWhere('to_url', 'like', "%{$search}%")))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'search' => $search,
            'editing' => null,
        ]);
    }

    public function edit(Redirect $redirect, Request $request): View
    {
        return view('admin.redirects.index', [
            'redirects' => Redirect::query()->latest('id')->paginate(20),
            'search' => '',
            'editing' => $redirect,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Redirect::create($this->validated($request));

        return redirect()->route('admin.redirects.index')->with('success', 'Redirect created.');
    }

    public function update(Request $request, Redirect $redirect): RedirectResponse
    {
        $redirect->update($this->validated($request, $redirect));

        return redirect()->route('admin.redirects.index')->with('success', 'Redirect updated.');
    }

    public function toggle(Redirect $redirect): RedirectResponse
    {
        $redirect->update(['is_active' => ! $redirect->is_active]);

        return back()->with('success', $redirect->is_active ? 'Redirect enabled.' : 'Redirect disabled.');
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        $redirect->delete();

        return redirect()->route('admin.redirects.index')->with('success', 'Redirect deleted.');
    }

    private function validated(Request $request, ?Redirect $ignore = null): array
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:191'],
            'to_url' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['from_path'] = Redirect::normalisePath($data['from_path']);

        // Uniqueness must be checked against the normalised value.
        validator(
            ['from_path' => $data['from_path']],
            ['from_path' => [Rule::unique('redirects', 'from_path')->ignore($ignore?->id)]],
        )->validate();

        if (! str_starts_with($data['to_url'], 'http')) {
            $data['to_url'] = Redirect::normalisePath($data['to_url']);
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }
}
