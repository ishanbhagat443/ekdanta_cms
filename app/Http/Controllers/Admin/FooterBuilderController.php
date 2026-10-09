<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layout;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Services\LayoutService;
use App\Services\SectionTypes\SectionTypeRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FooterBuilderController extends Controller
{
    public function __construct(
        protected LayoutService $layouts,
        protected SectionTypeRegistry $registry,
        protected MenuRepositoryInterface $menus,
    ) {
        $this->middleware('can:builder.footer');
    }

    public function edit(): View
    {
        $footer = $this->layouts->area(Layout::AREA_FOOTER);

        return view('admin.footer-builder.edit', [
            'footer' => $footer->load('sections'),
            'footerTypes' => $this->registry->forArea('footer'),
            'menus' => $this->menus->allMenus(),
            'registry' => $this->registry,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bg_color' => ['nullable', 'string', 'max:20'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'bg_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'side_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'overlay_opacity' => ['nullable', 'integer', 'between:0,95'],
            'container' => ['nullable', 'in:container,container-fluid'],
            'custom_css' => ['nullable', 'string', 'max:20000'],
            'custom_js' => ['nullable', 'string', 'max:20000'],
        ]);

        $this->layouts->updateArea(Layout::AREA_FOOTER, [
            'settings' => [
                'bg_color' => $data['bg_color'] ?? null,
                'text_color' => $data['text_color'] ?? null,
                'bg_media_id' => $data['bg_media_id'] ?? null,
                'side_media_id' => $data['side_media_id'] ?? null,
                'overlay_opacity' => $data['overlay_opacity'] ?? 70,
                'container' => $data['container'] ?? 'container',
            ],
            'custom_css' => $data['custom_css'] ?? null,
            // Footer JS runs on every public page: Developer Mode only. Without
            // it the key is left out, so LayoutService keeps the current script.
            ...(Gate::allows('developer.mode') ? ['custom_js' => $data['custom_js'] ?? null] : []),
        ]);

        return redirect()->route('admin.footer.edit')->with('success', 'Footer settings saved.');
    }
}
