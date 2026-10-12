<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Case study template: a guided zoom across the real Instagram grid,
 * ending on the whole grid; what we made lights up along the way (design:
 * Case-Study c-gallery). Reads the case being shown; on any other page,
 * the case named below.
 */
class CaseGallerySection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'case-gallery';
    }

    protected function anchor(): string
    {
        return 'c-gallery';
    }

    public function label(): string
    {
        return 'Ekdanta · Case Gallery Zoom';
    }

    protected function content(): array
    {
        return [
            $this->text('case', 'Case study to show when this is not the case-study template (slug)', 'trilok-infratech', 120, false),
            $this->text('eyebrow', 'Small line above the heading', 'WHAT WE MADE', 40),
            $this->text('heading', 'Heading', 'Every post, made in-house.', 80),
            $this->lines('text', 'Text', 'Design, copy and shoots by the Ekdanta team. Keep scrolling for a closer look at the grid.', 300),
            $this->text('zoom_label', 'Picture label while zoomed in', 'Zoom 130%', 30, false),
            $this->text('full_label', 'Picture label at the end', 'Full grid', 30, false),
            $this->text('image_alt', 'Picture description ({name} = the brand)', 'Instagram grid made by Ekdanta for {name}', 120, false),
        ];
    }
}
