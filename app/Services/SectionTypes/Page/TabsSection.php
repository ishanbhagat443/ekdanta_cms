<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class TabsSection extends PageSectionType
{
    public function key(): string
    {
        return 'tabs';
    }

    public function label(): string
    {
        return 'Tabs';
    }

    public function icon(): string
    {
        return 'fas fa-folder';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'tabs', 'label' => 'Tabs', 'type' => 'repeater', 'rules' => ['max:10'], 'fields' => [
                ['key' => 'title', 'label' => 'Tab Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                ['key' => 'body', 'label' => 'Tab Content', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:10000']],
            ]],
        ];
    }
}
