<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Collection;

interface MenuRepositoryInterface
{
    /** @return Collection<int, Menu> */
    public function allMenus(): Collection;

    public function findMenu(int $id): Menu;

    public function findBySlug(string $slug): ?Menu;

    /** Menu with full nested active item tree, eager-loaded 3 levels + mega-menu content. */
    public function tree(int $menuId): ?Menu;
}
