<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * "Why Choose Us" (design 07 Choose Us.png): serif heading with ornament,
 * intro subtext, large rounded showroom photo with a cream bullet strip
 * overlapping its bottom edge, and an optional gradient CTA button.
 */
class WhyChooseSection extends PageSectionType
{
    public function key(): string
    {
        return 'why-choose';
    }

    public function label(): string
    {
        return 'Why Choose Us (Photo + Bullet Strip)';
    }

    public function icon(): string
    {
        return 'fas fa-medal';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            $this->mediaField('image_media_id', 'Showroom Photo', device: false),
            ['key' => 'bullets', 'label' => 'Bullet Points (strip over photo)', 'type' => 'repeater', 'rules' => ['max:6'], 'fields' => [
                ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'rules' => ['required', 'string', 'max:150']],
            ]],
            ['key' => 'button_label', 'label' => 'Button Label', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:60']],
            ['key' => 'button_url', 'label' => 'Button URL', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            $this->buttonStyleField(),
            $this->mediaField('bg_media_id', 'Background Texture', device: false),
        ];
    }
}
