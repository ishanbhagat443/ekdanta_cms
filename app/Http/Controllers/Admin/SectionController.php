<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalBlock;
use App\Models\Layout;
use App\Models\Section;
use App\Services\GlobalBlockService;
use App\Services\SectionBuilderService;
use App\Support\SectionAnimation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SectionController extends Controller
{
    public function __construct(
        protected SectionBuilderService $builder,
        protected GlobalBlockService $globalBlocks,
    ) {
        $this->middleware('auth');
    }

    /** POST /admin/sections — add a section to a layout area or a page. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'area' => ['required', 'in:header,topbar,footer,page'],
            'page_id' => ['required_if:area,page', 'nullable', 'integer', 'exists:pages,id'],
            'type' => ['required', 'string', 'max:50'],
        ]);

        $this->authorizeArea($data['area']);

        $owner = $this->resolveOwner($data['area'], $data['page_id'] ?? null);
        $section = $this->builder->addSection($owner, $data['area'], $data['type']);

        return response()->json(['section' => $section], 201);
    }

    /** PUT /admin/sections/{section} — save one device payload + meta. */
    public function update(Request $request, Section $section): JsonResponse
    {
        $this->authorizeSection($section);

        $request->validate([
            'device' => ['required', 'in:desktop,tablet,mobile'],
            'data' => ['nullable', 'array'],
            'name' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'style' => ['nullable', 'array'],
            'style.visible.desktop' => ['nullable', 'boolean'],
            'style.visible.tablet' => ['nullable', 'boolean'],
            'style.visible.mobile' => ['nullable', 'boolean'],
            'style.width.desktop' => ['nullable', 'integer', 'between:1,12'],
            'style.width.tablet' => ['nullable', 'integer', 'between:1,12'],
            'style.width.mobile' => ['nullable', 'integer', 'between:1,12'],
            'style.bg_gradient' => ['nullable', 'string', 'exists:gradients,slug'],
            'style.bg_video_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'style.overlay_color' => ['nullable', 'string', 'max:30'],
            'style.overlay_opacity' => ['nullable', 'integer', 'between:0,95'],
            'style.radius' => ['nullable', 'regex:/^\d+(\.\d+)?(px|%|rem|em)?$/'],
            'style.z_index' => ['nullable', 'integer', 'between:-10,100'],
            // Scroll animation, per device. Values are clamped and whitelisted
            // by SectionAnimation below; this only checks the shape.
            'style.anim' => ['nullable', 'array'],
            'style.anim.*' => ['nullable', 'array'],
        ]);

        $input = $request->only(['device', 'data', 'name', 'is_active', 'style']);

        if (array_key_exists('style', $input)) {
            $input['style'] = Section::withoutPrivilegedStyle((array) $input['style']);

            if (array_key_exists('anim', $input['style'])) {
                $input['style']['anim'] = SectionAnimation::sanitiseAll((array) $input['style']['anim']);
            }
        }

        $section = $this->builder->updateSection($section, $input);

        return response()->json(['section' => $section]);
    }

    public function clone(Section $section): JsonResponse
    {
        $this->authorizeSection($section);

        return response()->json(['section' => $this->builder->cloneSection($section)], 201);
    }

    public function destroy(Section $section): JsonResponse
    {
        $this->authorizeSection($section);
        $this->builder->deleteSection($section);

        return response()->json(['deleted' => true]);
    }

    /** POST /admin/sections/from-block — "Add Existing Block" in the Page Builder. */
    public function storeFromBlock(Request $request): JsonResponse
    {
        $data = $request->validate([
            'area' => ['required', 'in:header,topbar,footer,page'],
            'page_id' => ['required_if:area,page', 'nullable', 'integer', 'exists:pages,id'],
            'global_block_id' => ['required', 'integer', 'exists:global_blocks,id'],
        ]);

        $this->authorizeArea($data['area']);

        $block = GlobalBlock::findOrFail($data['global_block_id']);
        $owner = $this->resolveOwner($data['area'], $data['page_id'] ?? null);
        $sortOrder = $owner->sections()->max('sort_order') + 1;

        $section = $this->globalBlocks->placeOnPage($block, $owner, $sortOrder);

        return response()->json(['section' => $section], 201);
    }

    /** POST /admin/sections/{section}/promote — turn a local section into a new reusable block. */
    public function promote(Request $request, Section $section): JsonResponse
    {
        $this->authorizeSection($section);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', 'exists:global_block_categories,id'],
        ]);

        Gate::authorize('blocks.manage');

        $block = $this->globalBlocks->createFromSection($section, $data);

        return response()->json(['block' => $block], 201);
    }

    /** POST /admin/sections/{section}/detach — freeze this instance's payload locally. */
    public function detach(Section $section): JsonResponse
    {
        $this->authorizeSection($section);

        return response()->json(['section' => $this->globalBlocks->detach($section)]);
    }

    /** POST /admin/sections/{section}/reattach — re-link to a master and pull its current content. */
    public function reattach(Request $request, Section $section): JsonResponse
    {
        $this->authorizeSection($section);

        $data = $request->validate(['global_block_id' => ['required', 'integer', 'exists:global_blocks,id']]);
        $block = GlobalBlock::findOrFail($data['global_block_id']);

        return response()->json(['section' => $this->globalBlocks->reattach($section, $block)]);
    }

    /** POST /admin/sections/reorder — drag-drop order for one area. */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'area' => ['required', 'in:header,topbar,footer,page'],
            'page_id' => ['required_if:area,page', 'nullable', 'integer', 'exists:pages,id'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $this->authorizeArea($data['area']);
        $this->builder->reorder($this->resolveOwner($data['area'], $data['page_id'] ?? null), $data['ids']);

        return response()->json(['reordered' => true]);
    }

    private function resolveOwner(string $area, ?int $pageId = null): Model
    {
        if ($area === 'page') {
            return \App\Models\Page::query()->findOrFail($pageId);
        }

        return Layout::query()->firstOrCreate(['area' => $area]);
    }

    /** header + topbar → builder.header; footer → builder.footer; page → pages.manage. */
    private function authorizeArea(string $area): void
    {
        Gate::authorize(match ($area) {
            'footer' => 'builder.footer',
            'page' => 'pages.manage',
            default => 'builder.header',
        });
    }

    private function authorizeSection(Section $section): void
    {
        $owner = $section->sectionable;

        $this->authorizeArea($owner instanceof Layout ? $owner->area : 'page');
    }
}
