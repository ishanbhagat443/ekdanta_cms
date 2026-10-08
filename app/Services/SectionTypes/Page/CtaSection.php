<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class CtaSection extends PageSectionType
{
    public function key(): string
    {
        return 'cta';
    }

    public function label(): string
    {
        return 'Call To Action';
    }

    public function icon(): string
    {
        return 'fas fa-bullseye';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            $this->richTextField('text', 'Text'),
            $this->buttonsRepeater(2),
            $this->alignmentField(),
        ];
    }
}
