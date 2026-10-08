<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class TimelineSection extends PageSectionType
{
    public function key(): string
    {
        return 'timeline';
    }

    public function label(): string
    {
        return 'Timeline';
    }

    public function icon(): string
    {
        return 'fas fa-history';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'milestones', 'label' => 'Milestones', 'type' => 'repeater', 'rules' => ['max:30'], 'fields' => [
                ['key' => 'year', 'label' => 'Year', 'type' => 'text', 'rules' => ['required', 'string', 'max:20']],
                ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'rules' => ['required', 'string', 'max:300']],
            ]],
        ];
    }
}
