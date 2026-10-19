<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Services: the case cards ride across the screen as the visitor scrolls,
 * tilting as they pass the centre (design: Services s-work). Cards:
 * Collections → Case studies (picture, sector, what we made).
 */
class ProofCarouselSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'proof-carousel';
    }

    protected function anchor(): string
    {
        return 's-work';
    }

    public function label(): string
    {
        return 'Ekdanta · Proof Carousel';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'PROOF', 40),
            $this->text('heading', 'Heading', 'Services, put to work.', 80),
            $this->lines('text', 'Text', 'Six of our 45+ brands, and what we made for each of them.', 300),
            $this->text('cases', 'Case studies (slugs, comma separated; empty = all)', 'trilok-infratech, yantra-design, heaven-solar, srk-dairy-farm-and-foods, her-and-he-cosmetics, valleyrides', 400, false),
            $this->text('image_alt', 'Picture description ({name} = the brand)', 'Social media work for {name}', 120, false),
            $this->text('link_label', 'Link', 'See all our work', 40),
            $this->link('link', 'Link goes to', '/work'),
        ];
    }

    /** Screen fit: parts its automatic rule places wrongly (see SectionType::fitParts). */
    public function fitParts(): array
    {
        return [
            '.ek-comp--d [data-m="proof-head"]' => 'L',
            '.ek-comp--d [data-m="proof-ui"]:has(a)' => 'R',
            '.ek-comp--d [data-m="proof-ui"]:not(:has(a))' => 'L',
        ];
    }
}
