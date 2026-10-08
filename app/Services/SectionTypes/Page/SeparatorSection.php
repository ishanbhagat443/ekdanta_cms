<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * Decorative image separator (Designs/Contact Us Page 02 Garland & 05
 * Floral). A single centered, responsive ornament image from the Media
 * Library with its own width + vertical margins. Reused for both the
 * garland and floral separators — the admin just picks a different image.
 * Section visibility / sort order / background / custom CSS class / ID /
 * Developer-Mode HTML-CSS editor all come from the shared Page Builder
 * section wrapper — nothing bespoke here.
 */
class SeparatorSection extends PageSectionType
{
    public function key(): string
    {
        return 'separator';
    }

    public function label(): string
    {
        return 'Decorative Separator (Garland / Floral)';
    }

    public function icon(): string
    {
        return 'fas fa-grip-lines';
    }

    public function fields(): array
    {
        return [
            $this->mediaField('media_id', 'Separator Image (garland / floral)'),
            ['key' => 'width', 'label' => 'Image Width (px)', 'type' => 'number', 'default' => 340, 'rules' => ['nullable', 'integer', 'between:40,1600']],
            ['key' => 'margin_top', 'label' => 'Margin Top (px)', 'type' => 'number', 'default' => 24, 'rules' => ['nullable', 'integer', 'between:0,400']],
            ['key' => 'margin_bottom', 'label' => 'Margin Bottom (px)', 'type' => 'number', 'default' => 24, 'rules' => ['nullable', 'integer', 'between:0,400']],
        ];
    }
}
