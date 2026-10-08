<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class FeaturesSection extends PageSectionType
{
    public function key(): string
    {
        return 'features';
    }

    public function label(): string
    {
        return 'Features / Icon Cards';
    }

    public function icon(): string
    {
        return 'fas fa-th-large';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'layout', 'label' => 'Layout', 'type' => 'select', 'default' => 'cards', 'options' => ['cards' => 'Bordered cards', 'bullets' => 'Icon bullets row'], 'rules' => ['nullable', 'in:cards,bullets']],
            ['key' => 'items', 'label' => 'Items', 'type' => 'repeater', 'rules' => ['max:12'], 'fields' => [
                ['key' => 'icon', 'label' => 'Icon class (fas fa-…)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60']],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:150']],
                ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            ]],
        ];
    }
}
