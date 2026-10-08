<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class BannerSection extends PageSectionType
{
    public function key(): string
    {
        return 'banner';
    }

    public function label(): string
    {
        return 'Banner (image + text strip)';
    }

    public function icon(): string
    {
        return 'far fa-image';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            $this->richTextField('text', 'Text'),
            $this->buttonsRepeater(1),
            $this->mediaField(),
            ['key' => 'image_position', 'label' => 'Image Position', 'type' => 'select', 'default' => 'right', 'options' => ['left' => 'Left', 'right' => 'Right'], 'rules' => ['nullable', 'in:left,right']],
        ];
    }
}
