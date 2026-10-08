<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class CardsSection extends PageSectionType
{
    public function key(): string
    {
        return 'cards';
    }

    public function label(): string
    {
        return 'Cards Grid';
    }

    public function icon(): string
    {
        return 'far fa-id-card';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'cards', 'label' => 'Cards', 'type' => 'repeater', 'rules' => ['max:12'], 'fields' => [
                ['key' => 'media_id', 'label' => 'Image', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id']],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:150']],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
                ['key' => 'link_label', 'label' => 'Link Label', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60']],
                ['key' => 'link_url', 'label' => 'Link URL', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            ]],
            ['key' => 'columns', 'label' => 'Columns (desktop)', 'type' => 'select', 'default' => '3', 'options' => ['2' => '2', '3' => '3', '4' => '4'], 'rules' => ['nullable', 'in:2,3,4']],
        ];
    }
}
