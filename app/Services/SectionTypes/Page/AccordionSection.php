<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class AccordionSection extends PageSectionType
{
    public function key(): string
    {
        return 'accordion';
    }

    public function label(): string
    {
        return 'Accordion';
    }

    public function icon(): string
    {
        return 'fas fa-layer-group';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'items', 'label' => 'Panels', 'type' => 'repeater', 'rules' => ['max:20'], 'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:200']],
                ['key' => 'body', 'label' => 'Body', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:5000']],
            ]],
            $this->mediaField('side_media_id', 'Side Image (optional)'),
            ['key' => 'first_open', 'label' => 'First panel open by default', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'heading_align', 'label' => 'Heading Alignment', 'type' => 'select', 'default' => 'center', 'options' => ['start' => 'Left', 'center' => 'Center', 'end' => 'Right'], 'rules' => ['nullable', 'in:start,center,end']],
            ['key' => 'heading_ornament', 'label' => 'Diamond ornament under the heading', 'type' => 'toggle', 'default' => false, 'rules' => ['nullable', 'boolean']],
            ['key' => 'panel_style', 'label' => 'Panel Style', 'type' => 'select', 'default' => 'default', 'options' => ['default' => 'Bootstrap default', 'cards' => 'Spaced white cards with +/− icons'], 'rules' => ['nullable', 'in:default,cards']],
        ];
    }
}
