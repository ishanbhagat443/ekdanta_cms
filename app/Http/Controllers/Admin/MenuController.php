<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Services\MenuService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        protected MenuRepositoryInterface $menus,
        protected MenuService $menuService,
    ) {
        $this->middleware('can:menus.manage');
    }

    public function index(): View
    {
        return view('admin.menus.index', [
            'menus' => $this->menus->allMenus(),
            'locations' => Menu::LOCATIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $menu = $this->menuService->createMenu($data);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu created.');
    }

    /** Nested drag-drop item editor. */
    public function edit(Menu $menu): View
    {
        $menu->load(['items' => fn ($q) => $q->orderBy('sort_order')->with('image')]);

        return view('admin.menus.edit', [
            'menu' => $menu,
            'tree' => $this->buildTree($menu),
            'locations' => Menu::LOCATIONS,
            'linkTypes' => \App\Models\MenuItem::LINK_TYPES,
            'columnTypes' => \App\Models\MenuItem::COLUMN_TYPES,
            'badgeColors' => \App\Models\MenuItem::BADGE_COLORS,
            'permissions' => \Spatie\Permission\Models\Permission::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $data = $this->validated($request, $menu);

        $this->menuService->updateMenu($menu, $data);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu saved.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->menuService->deleteMenu($menu);

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }

    /** POST /admin/menus/{menu}/tree — persist drag-drop nesting. */
    public function saveTree(Request $request, Menu $menu): JsonResponse
    {
        $data = $request->validate(['tree' => ['required', 'array']]);

        $this->menuService->reorderTree($menu, $data['tree']);

        return response()->json(['saved' => true]);
    }

    private function validated(Request $request, ?Menu $menu = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:menus,name,'.($menu?->id ?? 'NULL').',id'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'location' => ['nullable', 'in:'.implode(',', array_keys(Menu::LOCATIONS))],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function buildTree(Menu $menu): array
    {
        $byParent = $menu->items->groupBy('parent_id');

        $build = function (?int $parentId) use (&$build, $byParent): array {
            return ($byParent[$parentId] ?? collect())
                ->sortBy('sort_order')
                ->map(fn ($item) => ['item' => $item, 'children' => $build($item->id)])
                ->values()
                ->all();
        };

        return $build(null);
    }
}
