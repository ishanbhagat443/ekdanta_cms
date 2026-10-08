<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Header;

use App\Services\SectionTypes\SectionType;

class NavMenuSection extends SectionType
{
    public function key(): string
    {
        return 'nav-menu';
    }

    public function label(): string
    {
        return 'Navigation Menu';
    }

    public function icon(): string
    {
        return 'fas fa-bars';
    }

    public function allowedAreas(): array
    {
        return ['header', 'footer'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'menu_id', 'label' => 'Menu', 'type' => 'menu', 'rules' => ['required', 'integer', 'exists:menus,id'], 'hint' => 'Dropdowns and mega menus come from the menu tree (Menus module).'],
            ['key' => 'alignment', 'label' => 'Alignment', 'type' => 'select', 'default' => 'center', 'options' => ['start' => 'Left', 'center' => 'Center', 'end' => 'Right'], 'rules' => ['nullable', 'in:start,center,end']],
        ];
    }
}
