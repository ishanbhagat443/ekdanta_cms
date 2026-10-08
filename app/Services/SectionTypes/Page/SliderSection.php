<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class SliderSection extends PageSectionType
{
    public function key(): string
    {
        return 'slider';
    }

    public function label(): string
    {
        return 'Slider / Carousel';
    }

    public function icon(): string
    {
        return 'fas fa-images';
    }

    public function fields(): array
    {
        return [
            ['key' => 'slides', 'label' => 'Slides', 'type' => 'repeater', 'rules' => ['max:10'], 'fields' => [
                ['key' => 'media_id', 'label' => 'Image', 'type' => 'media', 'rules' => ['required', 'integer', 'exists:media,id']],
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:200']],
                ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
                ['key' => 'button_label', 'label' => 'Button Label', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60']],
                ['key' => 'button_url', 'label' => 'Button URL', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            ]],
            ['key' => 'autoplay', 'label' => 'Autoplay', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'interval', 'label' => 'Interval (ms)', 'type' => 'number', 'default' => 5000, 'rules' => ['nullable', 'integer', 'between:1000,20000']],
            ['key' => 'height', 'label' => 'Height', 'type' => 'select', 'default' => 'medium', 'options' => ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'], 'rules' => ['nullable', 'in:small,medium,large']],
        ];
    }
}
