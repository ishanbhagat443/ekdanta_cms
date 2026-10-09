<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * "Our Artistry Excellence" (About page design 06 Our Artistary.png): an
 * accordion-style photo gallery — collapsed cards show only the title,
 * clicking one reveals its description — followed by a ribbon-notch
 * icon-card strip beneath it. Both blocks are part of the same design
 * screenshot. The gallery has no practical item cap: 3 or fewer render as
 * a grid, more than 3 switch to a horizontal scroll track automatically.
 */
class ArtistrySection extends PageSectionType
{
    public function key(): string
    {
        return 'artistry';
    }

    public function label(): string
    {
        return 'Image Gallery + Icon Cards';
    }

    public function icon(): string
    {
        return 'fas fa-swatchbook';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'gallery', 'label' => 'Gallery Cards (3 or fewer = grid, more = horizontal scroll)', 'type' => 'repeater', 'rules' => ['max:24'], 'fields' => [
                ['key' => 'media_id', 'label' => 'Photo', 'type' => 'media', 'rules' => ['required', 'integer', 'exists:media,id']],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                ['key' => 'text', 'label' => 'Description (revealed when this card is selected)', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:300']],
            ]],
            ['key' => 'features', 'label' => 'Icon Card Strip', 'type' => 'repeater', 'rules' => ['max:6'], 'fields' => [
                ['key' => 'icon', 'label' => 'Icon class (fas fa-…)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60']],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:150']],
                ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:300']],
            ]],
            $this->mediaField('bg_media_id', 'Background Texture', device: false),
            ['key' => 'show_flourish', 'label' => 'Gold flourish ornaments beside the icon cards', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
