<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Menu;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MenuRepository implements MenuRepositoryInterface
{
    public function allMenus(): Collection
    {
        return Menu::query()->withCount('items')->orderBy('name')->get();
    }

    public function findMenu(int $id): Menu
    {
        return Menu::query()->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Menu
    {
        return Menu::query()->where('slug', $slug)->active()->first();
    }

    public function tree(int $menuId): ?Menu
    {
        // Eager-load everything a mega-menu column can render, plus 3 levels
        // of items, so the frontend never lazy-loads (preventLazyLoading is ON).
        $withReference = fn ($q) => $q->where('is_active', true)
            ->with(['image']);

        return Menu::query()
            ->with([
                'rootItems' => $withReference,
                'rootItems.children' => $withReference,
                'rootItems.children.children' => $withReference,
            ])
            ->find($menuId);
    }
}
