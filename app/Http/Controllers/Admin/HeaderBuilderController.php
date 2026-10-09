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

class HeaderBuilderController extends Controller
{
    public function __construct(
        protected LayoutService $layouts,
        protected SectionTypeRegistry $registry,
        protected MenuRepositoryInterface $menus,
    ) {
        $this->middleware('can:builder.header');
    }

    /** One page managing both the top bar strip and the main header. */
    public function edit(): View
    {
        $header = $this->layouts->area(Layout::AREA_HEADER);
        $topbar = $this->layouts->area(Layout::AREA_TOPBAR);

        return view('admin.header-builder.edit', [
            'header' => $header->load('sections'),
            'topbar' => $topbar->load('sections'),
            'headerTypes' => $this->registry->forArea('header'),
            'topbarTypes' => $this->registry->forArea('topbar'),
            'menus' => $this->menus->allMenus(),
            'registry' => $this->registry,
        ]);
    }

    /** Layout-level settings: sticky, container, custom CSS/JS. */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sticky' => ['nullable', 'boolean'],
            'container' => ['nullable', 'in:container,container-fluid'],
            'show_topbar' => ['nullable', 'boolean'],
            'variant' => ['nullable', 'in:normal,overlay,pill,hidden'],
            'progress_bar' => ['nullable', 'boolean'],
            'progress_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'whatsapp_button' => ['nullable', 'in:off,mobile,all'],
            'overlay_cta_label' => ['nullable', 'string', 'max:60'],
            'overlay_cta_url' => ['nullable', 'string', 'max:500'],
            'overlay_call_line' => ['nullable', 'boolean'],
            'overlay_pause_label' => ['nullable', 'string', 'max:40'],
            'nav_style' => ['nullable', 'array'],
            'nav_style.*' => ['nullable', 'in:inline,dropdown,overlay'],
            // Header-over-hero (design 05): transparent overlay → solid on scroll.
            'overlay_hero' => ['nullable', 'boolean'],
            'transparent_color' => ['nullable', 'string', 'max:30'],
            'scrolled_color' => ['nullable', 'string', 'max:30'],
            'blur' => ['nullable', 'integer', 'between:0,30'],
            'scrolled_shadow' => ['nullable', 'boolean'],
            'menu_color' => ['nullable', 'string', 'max:30'],
            'menu_hover_color' => ['nullable', 'string', 'max:30'],
            'custom_css' => ['nullable', 'string', 'max:20000'],
            'custom_js' => ['nullable', 'string', 'max:20000'],
        ]);

        // A post without "variant" (older forms, scripts) still means what its overlay switch says.
        $variant = $data['variant'] ?? (! empty($data['overlay_hero']) ? 'overlay' : 'normal');

        $this->layouts->updateArea(Layout::AREA_HEADER, [
            'settings' => [
                'sticky' => (bool) ($data['sticky'] ?? false),
                'container' => $data['container'] ?? 'container',
                'show_topbar' => (bool) ($data['show_topbar'] ?? true),
                'variant' => $variant,
                'progress_bar' => (bool) ($data['progress_bar'] ?? false),
                'progress_color' => $data['progress_color'] ?? null,
                'whatsapp_button' => $data['whatsapp_button'] ?? 'off',
                'overlay_cta_label' => $data['overlay_cta_label'] ?? null,
                'overlay_cta_url' => $data['overlay_cta_url'] ?? null,
                'overlay_call_line' => (bool) ($data['overlay_call_line'] ?? false),
                'overlay_pause_label' => $data['overlay_pause_label'] ?? null,
                'transparent_color' => $data['transparent_color'] ?? null,
                'scrolled_color' => $data['scrolled_color'] ?? null,
                'blur' => $data['blur'] ?? 0,
                'scrolled_shadow' => (bool) ($data['scrolled_shadow'] ?? true),
                'menu_color' => $data['menu_color'] ?? null,
                'menu_hover_color' => $data['menu_hover_color'] ?? null,
                'nav_style' => $data['nav_style'] ?? [], // defaults: Layout::navStyle()
            ],
            'custom_css' => $data['custom_css'] ?? null,
            // Header JS runs on every public page: Developer Mode only. Without
            // it the key is left out, so LayoutService keeps the current script.
            ...(Gate::allows('developer.mode') ? ['custom_js' => $data['custom_js'] ?? null] : []),
        ]);

        return redirect()->route('admin.header.edit')->with('success', 'Header settings saved.');
    }
}
