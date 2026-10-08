<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * "The Founder's Journey" (About page design 03 Founder Journey.png):
 * centered serif heading + two lead paragraphs over a botanical-illustrated
 * damask backdrop, with a tall portrait bleeding off the right edge, and a
 * two-card Mission / Vision row beneath.
 */
class FounderJourneySection extends PageSectionType
{
    public function key(): string
    {
        return 'founder-journey';
    }

    public function label(): string
    {
        return 'Founder\'s Journey (Story + Mission/Vision)';
    }

    public function icon(): string
    {
        return 'fas fa-scroll';
    }

    public function fields(): array
    {
        return [
            ['key' => 'heading', 'label' => 'Heading', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:300']],
            $this->richTextField('body', 'Story (paragraphs)'),
            $this->mediaField('bg_media_id', 'Background Artwork (damask texture)', device: false),
            $this->mediaField('image_media_id', 'Portrait Photo (right edge)', device: false),
            $this->mediaField('ornament_top_media_id', 'Decorative Illustration — Top Left', device: false),
            $this->mediaField('ornament_bottom_media_id', 'Decorative Illustration — Bottom Left', device: false),
            ['key' => 'mission_title', 'label' => 'Mission Card — Title', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'mission_text', 'label' => 'Mission Card — Text', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
            ['key' => 'vision_title', 'label' => 'Vision Card — Title', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'vision_text', 'label' => 'Vision Card — Text', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
            ['key' => 'show_garland', 'label' => 'Gold garland border along the top edge', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
