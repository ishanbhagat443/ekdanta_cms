<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * About section with photo collage (design 04 About.png): text column on
 * the left; on the right three photos registered into the frame outlines
 * painted in the background artwork (arch, oval, rectangle) plus three
 * floating caption labels beside the artwork's hand-drawn arrows.
 */
class AboutCollageSection extends PageSectionType
{
    public function key(): string
    {
        return 'about-collage';
    }

    public function label(): string
    {
        return 'About (Photo Collage)';
    }

    public function icon(): string
    {
        return 'fas fa-images';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(withEyebrow: true),
            $this->richTextField('body', 'Body Text'),
            ['key' => 'button_label', 'label' => 'Button Label', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60']],
            ['key' => 'button_url', 'label' => 'Button URL', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            $this->buttonStyleField(),
            $this->mediaField('bg_media_id', 'Background Artwork (carpet, frames & arrows)', device: false),
            $this->mediaField('image_arch_media_id', 'Photo — Arch Frame (right)', device: false),
            $this->mediaField('image_oval_media_id', 'Photo — Oval Frame (center)', device: false),
            $this->mediaField('image_rect_media_id', 'Photo — Rectangle Frame (behind oval)', device: false),
            ['key' => 'caption_top', 'label' => 'Floating Caption — Top', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120']],
            ['key' => 'caption_left', 'label' => 'Floating Caption — Left', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120']],
            ['key' => 'caption_bottom', 'label' => 'Floating Caption — Bottom', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120']],
        ];
    }
}
