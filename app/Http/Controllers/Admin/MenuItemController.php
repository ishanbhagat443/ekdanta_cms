<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function __construct(protected MenuService $menuService)
    {
        $this->middleware('can:menus.manage');
    }

    public function store(Request $request, Menu $menu): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(['item' => $this->menuService->createItem($menu, $data)->load('image')], 201);
    }

    public function update(Request $request, MenuItem $item): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(['item' => $this->menuService->updateItem($item, $data)->load('image')]);
    }

    public function destroy(MenuItem $item): JsonResponse
    {
        $this->menuService->deleteItem($item);

        return response()->json(['deleted' => true]);
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(): array
    {
        $linkTypes = implode(',', array_keys(MenuItem::LINK_TYPES));
        $columnTypes = implode(',', array_keys(MenuItem::COLUMN_TYPES));
        $badgeColors = implode(',', MenuItem::BADGE_COLORS);

        return [
            'label' => ['required', 'string', 'max:100'],
            'nav_label' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'link_type' => ['nullable', 'in:'.$linkTypes],
            'url' => ['nullable', 'string', 'max:500'],
            'reference_id' => ['nullable', 'integer', 'required_if:link_type,page,product_category,product,blog,blog_category'],
            'target' => ['nullable', 'in:_self,_blank'],
            'icon' => ['nullable', 'string', 'max:50'],
            'css_class' => ['nullable', 'string', 'max:150'],
            'element_id' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z][\w-]*$/'],
            'badge_text' => ['nullable', 'string', 'max:40'],
            'badge_color' => ['nullable', 'in:'.$badgeColors],
            'permission' => ['nullable', 'string', 'max:100', 'exists:permissions,name'],
            'auth_only' => ['nullable', 'boolean'],
            'guest_only' => ['nullable', 'boolean'],
            'is_mega' => ['nullable', 'boolean'],
            'mega_columns' => ['nullable', 'integer', 'between:1,6'],
            'column_type' => ['nullable', 'in:'.$columnTypes],
            'image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'custom_html' => ['nullable', 'string', 'max:5000'],
            'cta_label' => ['nullable', 'string', 'max:60'],
            'cta_url' => ['nullable', 'string', 'max:500'],
            'cta_style' => ['nullable', 'in:primary,outline,link'],
            'featured_limit' => ['nullable', 'integer', 'between:1,12'],
            'visible_devices' => ['nullable', 'array'],
            'visible_devices.*' => ['in:desktop,tablet,mobile'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
