<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Concerns\GeneratesUniqueSlug;
use Illuminate\Validation\ValidationException;

class MenuService
{
    use GeneratesUniqueSlug;

    /** Menu items may nest 3 levels deep (root → dropdown/column → link). */
    private const MAX_DEPTH = 2;

    public function __construct(
        protected \App\Repositories\Contracts\MenuRepositoryInterface $menus,
        protected LayoutService $layouts,
    ) {
    }

    public function createMenu(array $data): Menu
    {
        $menu = Menu::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlugFor(Menu::class, $data['slug'] ?? $data['name']),
            'location' => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $this->layouts->flushCache();

        return $menu;
    }

    public function updateMenu(Menu $menu, array $data): Menu
    {
        $attributes = [
            'name' => $data['name'],
            'location' => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? $menu->is_active),
        ];

        if (! empty($data['slug']) && $data['slug'] !== $menu->slug) {
            $attributes['slug'] = $this->uniqueSlugFor(Menu::class, $data['slug'], $menu->id);
        }

        $menu->update($attributes);
        $this->layouts->flushCache();

        return $menu;
    }

    /** @deprecated kept for the quick-rename form; prefer updateMenu() */
    public function renameMenu(Menu $menu, string $name): Menu
    {
        return $this->updateMenu($menu, ['name' => $name, 'location' => $menu->location, 'description' => $menu->description, 'is_active' => $menu->is_active]);
    }

    public function deleteMenu(Menu $menu): void
    {
        $menu->delete(); // items cascade
        $this->layouts->flushCache();
    }

    public function createItem(Menu $menu, array $data): MenuItem
    {
        $parentId = $data['parent_id'] ?? null;

        if ($parentId !== null) {
            $this->assertParentDepth((int) $parentId);
        }

        $item = $menu->items()->create($this->attributes($data) + [
            'parent_id' => $parentId,
            'sort_order' => (int) $menu->items()->where('parent_id', $parentId)->max('sort_order') + 1,
        ]);

        $this->layouts->flushCache();

        return $item;
    }

    public function updateItem(MenuItem $item, array $data): MenuItem
    {
        $item->update($this->attributes($data, $item));
        $this->layouts->flushCache();

        return $item->refresh();
    }

    public function deleteItem(MenuItem $item): void
    {
        $item->delete(); // children cascade
        $this->layouts->flushCache();
    }

    /**
     * Persist a full nested tree from the drag-drop editor.
     * $tree: [{id, children: [{id, children: […]}]}, …]
     */
    public function reorderTree(Menu $menu, array $tree): void
    {
        $this->applyTree($menu, $tree, null);
        $this->layouts->flushCache();
    }

    /** Shared create/update attribute mapping — every field is optional. */
    private function attributes(array $data, ?MenuItem $existing = null): array
    {
        $linkType = $data['link_type'] ?? $existing?->link_type ?? 'url';
        $needsReference = array_key_exists($linkType, MenuItem::REFERENCE_TYPES);

        return [
            'label' => $data['label'] ?? $existing?->label,
            'nav_label' => array_key_exists('nav_label', $data) ? $data['nav_label'] : $existing?->nav_label,
            'link_type' => $linkType,
            'url' => $needsReference ? null : (array_key_exists('url', $data) ? $data['url'] : $existing?->url),
            'reference_id' => $needsReference ? ($data['reference_id'] ?? $existing?->reference_id) : null,
            'reference_type' => $needsReference ? MenuItem::REFERENCE_TYPES[$linkType] : null,
            'target' => $data['target'] ?? $existing?->target ?? '_self',
            'icon' => array_key_exists('icon', $data) ? $data['icon'] : $existing?->icon,
            'css_class' => array_key_exists('css_class', $data) ? $data['css_class'] : $existing?->css_class,
            'element_id' => array_key_exists('element_id', $data) ? $data['element_id'] : $existing?->element_id,
            'badge_text' => array_key_exists('badge_text', $data) ? $data['badge_text'] : $existing?->badge_text,
            'badge_color' => array_key_exists('badge_color', $data) ? $data['badge_color'] : $existing?->badge_color,
            'permission' => array_key_exists('permission', $data) ? $data['permission'] : $existing?->permission,
            'auth_only' => (bool) ($data['auth_only'] ?? $existing?->auth_only ?? false),
            'guest_only' => (bool) ($data['guest_only'] ?? $existing?->guest_only ?? false),
            'is_mega' => (bool) ($data['is_mega'] ?? $existing?->is_mega ?? false),
            'mega_columns' => (int) ($data['mega_columns'] ?? $existing?->mega_columns ?? 4),
            'column_type' => array_key_exists('column_type', $data) ? $data['column_type'] : $existing?->column_type,
            'image_media_id' => array_key_exists('image_media_id', $data) ? $data['image_media_id'] : $existing?->image_media_id,
            'custom_html' => array_key_exists('custom_html', $data) ? $data['custom_html'] : $existing?->custom_html,
            'cta_label' => array_key_exists('cta_label', $data) ? $data['cta_label'] : $existing?->cta_label,
            'cta_url' => array_key_exists('cta_url', $data) ? $data['cta_url'] : $existing?->cta_url,
            'cta_style' => array_key_exists('cta_style', $data) ? $data['cta_style'] : $existing?->cta_style,
            'featured_limit' => (int) ($data['featured_limit'] ?? $existing?->featured_limit ?? 4),
            'visible_devices' => array_key_exists('visible_devices', $data) ? $data['visible_devices'] : $existing?->visible_devices,
            'is_active' => (bool) ($data['is_active'] ?? $existing?->is_active ?? true),
        ];
    }

    private function applyTree(Menu $menu, array $nodes, ?int $parentId, int $depth = 0): void
    {
        if ($depth > self::MAX_DEPTH) {
            throw ValidationException::withMessages(['tree' => 'Menus support a maximum depth of 3 levels.']);
        }

        foreach (array_values($nodes) as $position => $node) {
            $menu->items()
                ->where('id', (int) $node['id'])
                ->update(['parent_id' => $parentId, 'sort_order' => $position]);

            if (! empty($node['children'])) {
                $this->applyTree($menu, $node['children'], (int) $node['id'], $depth + 1);
            }
        }
    }

    private function assertParentDepth(int $parentId): void
    {
        $depth = 0;
        $node = MenuItem::find($parentId);

        while ($node !== null && $node->parent_id !== null) {
            $depth++;
            $node = $node->parent;
        }

        if ($depth >= self::MAX_DEPTH) {
            throw ValidationException::withMessages(['parent_id' => 'Menus support a maximum depth of 3 levels.']);
        }
    }
}
