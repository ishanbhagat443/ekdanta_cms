<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * "About Us" story section (About page design 02 About
 * Section.png): eyebrow + serif heading + intro paragraph on the left,
 * two lead paragraphs on the right, a full-width photo beneath both, two
 * closing paragraphs below the photo, and a pill-shaped highlights bar
 * with short taglines separated by dividers.
 */
class AboutStorySection extends PageSectionType
{
    public function key(): string
    {
        return 'about-story';
    }

    public function label(): string
    {
        return 'About Story (Text Grid + Photo + Highlights)';
    }

    public function icon(): string
    {
        return 'fas fa-book-open';
    }

    public function fields(): array
    {
        return [
            ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:150']],
            ['key' => 'heading', 'label' => 'Heading', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:300']],
            $this->richTextField('intro', 'Intro Paragraph (below heading, left column)'),
            $this->richTextField('lead', 'Lead Paragraphs (right column, top)'),
            $this->mediaField('photo_media_id', 'Full-Width Photo', device: false),
            $this->richTextField('body_left', 'Body Paragraph — Left (below photo)'),
            $this->richTextField('body_right', 'Body Paragraph — Right (below photo)'),
            ['key' => 'highlights', 'label' => 'Highlights Bar (short taglines)', 'type' => 'repeater', 'rules' => ['max:6'], 'fields' => [
                ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'rules' => ['required', 'string', 'max:150']],
            ]],
            $this->mediaField('bg_media_id', 'Background Texture', device: false),
            ['key' => 'show_garland', 'label' => 'Gold garland border along the top edge', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
