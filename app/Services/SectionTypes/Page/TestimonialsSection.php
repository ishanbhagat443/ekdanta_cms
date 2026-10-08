<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class TestimonialsSection extends PageSectionType
{
    public function key(): string
    {
        return 'testimonials';
    }

    public function label(): string
    {
        return 'Testimonials';
    }

    public function icon(): string
    {
        return 'fas fa-quote-right';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'items', 'label' => 'Testimonials', 'type' => 'repeater', 'rules' => ['max:12'], 'fields' => [
                ['key' => 'media_id', 'label' => 'Photo', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id']],
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                ['key' => 'role', 'label' => 'Role / Company', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
                ['key' => 'quote', 'label' => 'Quote', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:1000']],
                ['key' => 'rating', 'label' => 'Rating 1-5', 'type' => 'text', 'rules' => ['nullable', 'integer', 'between:1,5']],
            ]],
            ['key' => 'carousel', 'label' => 'Show as carousel', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
