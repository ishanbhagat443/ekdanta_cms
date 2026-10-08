<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class ImageBlockSection extends PageSectionType
{
    public function key(): string
    {
        return 'image-block';
    }

    public function label(): string
    {
        return 'Image Block';
    }

    public function icon(): string
    {
        return 'fas fa-image';
    }

    public function fields(): array
    {
        return [
            $this->mediaField(),
            ['key' => 'alt', 'label' => 'Alt Text', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:191']],
            ['key' => 'caption', 'label' => 'Caption', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:300']],
            ['key' => 'link', 'label' => 'Link URL (optional)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            ['key' => 'width', 'label' => 'Width', 'type' => 'select', 'default' => 'container', 'options' => ['full' => 'Full width', 'container' => 'Container', 'narrow' => 'Narrow'], 'rules' => ['nullable', 'in:full,container,narrow']],
            $this->alignmentField(),
        ];
    }
}
