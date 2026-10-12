<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Work: reels. Five phones (Collections → Reels) rise into an arc, then
 * take turns in the spotlight (design: Work w-reels).
 */
class ReelsArcSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'reels-arc';
    }

    protected function anchor(): string
    {
        return 'w-reels';
    }

    public function label(): string
    {
        return 'Ekdanta · Reels Arc';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'REELS', 40),
            $this->text('heading', 'Heading', 'Reels that stop the scroll.', 80),
            $this->lines('text', 'Text', 'Scripted, shot and edited by our in-house team.', 200),
            $this->text('reels', 'Reels (slugs, comma separated; empty = the first five)', '', 400, false),
            $this->text('reel_label', 'Each phone: name read by screen readers ({title}, {brand})', 'Watch {title} by {brand} on Instagram', 120, false),
            $this->text('link_label', 'Link under the phones', 'Watch more on Instagram @ekdanta.in', 80),
            $this->link('link', 'Link goes to', 'https://www.instagram.com/ekdanta.in/'),
        ];
    }
}
