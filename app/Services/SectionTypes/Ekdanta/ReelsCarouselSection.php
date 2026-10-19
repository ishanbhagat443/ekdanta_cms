<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/** Home: phones rise from the bottom, then each reel takes the centre in turn (design: j-reels / m-reels). Reels: Collections → Reels. */
class ReelsCarouselSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'reels-carousel';
    }

    protected function anchor(): string
    {
        return 'j-reels';
    }

    public function label(): string
    {
        return 'Ekdanta · Reels Carousel';
    }

    protected function content(): array
    {
        return [
            $this->text('heading', 'Heading', 'Reels that stop the scroll.', 120),
            $this->lines('text', 'Text', 'Scripted, shot and edited by our in-house team. Keep scrolling to watch them one by one.', 300),
            $this->text('link_label', 'Link', 'Watch on Instagram @ekdanta.in', 80),
            $this->link('link', 'Link address (and for reels without their own)', 'https://www.instagram.com/ekdanta.in/'),
            $this->text('reel_label', 'Reel description for screen readers ({title}, {brand})', 'Watch {title} by {brand} on Instagram', 120, false),
            $this->text('reels', 'Reels to show (slugs, comma separated; empty = all, in their order)', null, 600, false),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => ['link_label' => 'Watch on Instagram']];
    }

    /** Screen fit: parts its automatic rule places wrongly (see SectionType::fitParts). */
    public function fitParts(): array
    {
        return [
            '[data-m="reel-wall"]' => 'Z',
            '.ek-comp--d [data-m="reel-late"]' => 'C',
        ];
    }
}
