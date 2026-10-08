<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class CounterSection extends PageSectionType
{
    public function key(): string
    {
        return 'counter';
    }

    public function label(): string
    {
        return 'Counters / Stats';
    }

    public function icon(): string
    {
        return 'fas fa-sort-numeric-up';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'counters', 'label' => 'Counters', 'type' => 'repeater', 'rules' => ['max:8'], 'fields' => [
                ['key' => 'number', 'label' => 'Number', 'type' => 'text', 'rules' => ['required', 'string', 'max:20']],
                ['key' => 'suffix', 'label' => 'Suffix (+ / k+ / %)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:10']],
                ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'rules' => ['required', 'string', 'max:150']],
            ]],
            ['key' => 'animate', 'label' => 'Animate count-up', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'show_ornament', 'label' => 'Gold garland border along the top edge', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
