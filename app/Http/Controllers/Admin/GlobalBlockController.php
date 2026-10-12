<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGlobalBlockRequest;
use App\Models\GlobalBlock;
use App\Models\GlobalBlockCategory;
use App\Models\GlobalBlockVersion;
use App\Repositories\Contracts\GlobalBlockRepositoryInterface;
use App\Services\GlobalBlockService;
use App\Services\SectionTypes\SectionTypeRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GlobalBlockController extends Controller
{
    public function __construct(
        protected GlobalBlockRepositoryInterface $blocks,
        protected GlobalBlockService $service,
        protected SectionTypeRegistry $registry,
    ) {
        $this->middleware('can:blocks.manage');
    }

    /** Library screen: search/filter/sort. */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'category', 'type', 'status', 'favorites', 'sort']);

        return view('admin.global-blocks.index', [
            'blocks' => $this->blocks->paginateForAdmin($filters, 20),
            'filters' => $filters,
            'categories' => GlobalBlockCategory::orderBy('sort_order')->orderBy('name')->get(),
            'sectionTypes' => $this->registry->all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.global-blocks.create', [
            'categories' => GlobalBlockCategory::orderBy('sort_order')->orderBy('name')->get(),
            'sectionTypes' => $this->registry->all(),
        ]);
    }

    public function store(StoreGlobalBlockRequest $request): RedirectResponse
    {
        $block = $this->service->create($request->validated());

        return redirect()->route('admin.global-blocks.edit', $block)->with('success', 'Block created — fill in its content below.');
    }

    public function edit(GlobalBlock $block): View
    {
        $block->load(['category', 'previewMedia', 'versions.author', 'instances.sectionable']);

        return view('admin.global-blocks.edit', [
            'block' => $block,
            'type' => $this->registry->find($block->type),
            'categories' => GlobalBlockCategory::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Bare-layout preview: renders the block through the SAME frontend
     * section-wrapper/section-type Blade a real page uses, via a transient
     * (never-saved) Section built from the block's current payload — this
     * guarantees pixel-parity with production rendering, not a re-implementation.
     */
    public function preview(GlobalBlock $block): View
    {
        $section = new \App\Models\Section([
            'type' => $block->type,
            'name' => $block->name,
            'is_active' => true,
            'data_desktop' => $block->data_desktop,
            'data_tablet' => $block->data_tablet,
            'data_mobile' => $block->data_mobile,
            'style' => $block->style,
        ]);
        $section->id = $block->id;
        $section->exists = true; // lets Blade helpers that key off ->id behave normally

        $mediaMap = [];
        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            foreach ($section->dataFor($device) as $key => $value) {
                if (str_ends_with((string) $key, 'media_id') && is_numeric($value)) {
                    $media = \App\Models\Media::find((int) $value);
                    if ($media) {
                        $mediaMap[(int) $value] = $media;
                    }
                }
            }
        }

        $menuId = $section->dataFor('desktop')['menu_id'] ?? null;
        $menu = $menuId ? app(\App\Repositories\Contracts\MenuRepositoryInterface::class)->tree((int) $menuId) : null;

        return view('admin.global-blocks.preview', [
            'section' => $section,
            'menu' => $menu,
            'mediaMap' => $mediaMap,
        ]);
    }

    /** AJAX save — same device/style shape as SectionController::update(). */
    public function update(Request $request, GlobalBlock $block): JsonResponse
    {
        $type = $this->registry->find($block->type);

        $request->validate([
            'device' => ['required', 'in:desktop,tablet,mobile'],
            'data' => ['nullable', 'array'],
            'style' => ['nullable', 'array'],
            'propagate' => ['nullable', 'boolean'],
        ]);

        $device = $request->string('device')->toString();
        $payload = [];

        if ($request->has('data') && is_array($request->input('data'))) {
            if ($device === 'desktop') {
                $payload['data_desktop'] = $request->input('data'); // validated in GlobalBlockService::update()
            } else {
                $allowed = $type->deviceKeys();
                $sparse = array_intersect_key((array) $request->input('data'), array_flip($allowed));
                $payload['data_'.$device] = array_filter($sparse, fn ($v) => $v !== null && $v !== '');
            }
        }

        if ($request->has('style')) {
            // A synced block reaches every page it is on: same CSS/JS rule as sections.
            $payload['style'] = \App\Models\Section::withoutPrivilegedStyle((array) $request->input('style'));
        }

        $block = $this->service->update($block, $payload, (bool) $request->boolean('propagate', true));

        return response()->json(['block' => $block]);
    }

    public function updateMeta(StoreGlobalBlockRequest $request, GlobalBlock $block): RedirectResponse
    {
        $this->service->update($block, $request->validated(), propagate: false);

        return redirect()->route('admin.global-blocks.edit', $block)->with('success', 'Block details saved.');
    }

    public function toggleFavorite(GlobalBlock $block): JsonResponse
    {
        $block = $this->service->toggleFavorite($block);

        return response()->json(['is_favorite' => $block->is_favorite]);
    }

    public function duplicate(GlobalBlock $block): RedirectResponse
    {
        $copy = $this->service->duplicate($block);

        return redirect()->route('admin.global-blocks.edit', $copy)->with('success', 'Block duplicated.');
    }

    public function destroy(GlobalBlock $block): RedirectResponse
    {
        $this->service->delete($block);

        return redirect()->route('admin.global-blocks.index')->with('success', 'Block deleted — pages using it keep their current content as a local copy.');
    }

    /* ── Version history ─────────────────────────────────────────── */

    public function restoreVersion(GlobalBlock $block, GlobalBlockVersion $version): RedirectResponse
    {
        $this->service->restoreVersion($block, $version);

        return redirect()->route('admin.global-blocks.edit', $block)->with('success', 'Previous version restored and pushed to linked pages.');
    }

    /* ── Export / Import ─────────────────────────────────────────── */

    public function export(Request $request, GlobalBlock $block): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $payload = $this->service->exportPayload($block, $request->boolean('bundle'));

        return response()->streamDownload(
            fn () => print (json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
            $block->slug.'-block.json',
            ['Content-Type' => 'application/json'],
        );
    }

    public function import(Request $request): RedirectResponse
    {
        // A bundled export carries its media files, so it can be large.
        $request->validate(['file' => ['required', 'file', 'mimes:json,txt', 'max:'.config('gsm.media.max_upload_kb')]]);

        $decoded = json_decode((string) file_get_contents($request->file('file')->getRealPath()), true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['file' => 'That file is not a valid block export.']);
        }

        $block = $this->service->import($decoded);

        return redirect()->route('admin.global-blocks.edit', $block)->with('success', 'Block imported as a draft — review then activate it.');
    }
}
