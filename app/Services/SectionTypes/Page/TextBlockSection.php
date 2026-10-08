<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class TextBlockSection extends PageSectionType
{
    public function key(): string
    {
        return 'text-block';
    }

    public function label(): string
    {
        return 'Text Block (rich text)';
    }

    public function icon(): string
    {
        return 'fas fa-align-left';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            $this->richTextField(),
            $this->buttonsRepeater(2),
            $this->alignmentField(),
            ['key' => 'width', 'label' => 'Width', 'type' => 'select', 'default' => 'container', 'options' => ['container' => 'Container', 'narrow' => 'Narrow (reading width)'], 'rules' => ['nullable', 'in:container,narrow']],
        ];
    }
}
