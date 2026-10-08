<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class GallerySection extends PageSectionType
{
    public function key(): string
    {
        return 'gallery';
    }

    public function label(): string
    {
        return 'Image Gallery';
    }

    public function icon(): string
    {
        return 'far fa-images';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'images', 'label' => 'Images', 'type' => 'repeater', 'rules' => ['max:24'], 'fields' => [
                ['key' => 'media_id', 'label' => 'Image', 'type' => 'media', 'rules' => ['required', 'integer', 'exists:media,id']],
                ['key' => 'caption', 'label' => 'Caption', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:191']],
            ]],
            ['key' => 'columns', 'label' => 'Columns (desktop)', 'type' => 'select', 'default' => '3', 'options' => ['2' => '2', '3' => '3', '4' => '4', '6' => '6'], 'rules' => ['nullable', 'in:2,3,4,6']],
            ['key' => 'lightbox', 'label' => 'Open in lightbox', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
