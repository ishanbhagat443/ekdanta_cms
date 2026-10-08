<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class FaqSection extends PageSectionType
{
    public function key(): string
    {
        return 'faq';
    }

    public function label(): string
    {
        return 'FAQ';
    }

    public function icon(): string
    {
        return 'far fa-question-circle';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'items', 'label' => 'Questions', 'type' => 'repeater', 'rules' => ['max:30'], 'fields' => [
                ['key' => 'question', 'label' => 'Question', 'type' => 'text', 'rules' => ['required', 'string', 'max:300']],
                ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:5000']],
            ]],
            ['key' => 'schema_enabled', 'label' => 'Output FAQ schema (SEO)', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
