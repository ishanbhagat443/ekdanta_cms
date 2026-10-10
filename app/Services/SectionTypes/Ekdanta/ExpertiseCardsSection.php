<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Services: one card per service group. A deck rises, fans out like a hand
 * of cards, deals into a row and flips face up; a card jumps to its group in
 * the Service Deep Dive (design: Services s-menu). Cards: Collections →
 * Service groups (card colours) and their grid services.
 */
class ExpertiseCardsSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'expertise-cards';
    }

    protected function anchor(): string
    {
        return 's-menu';
    }

    public function label(): string
    {
        return 'Ekdanta · Expertise Cards';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'WHAT WE DO', 40),
            $this->text('heading', 'Heading', 'Four expertise. One team.', 80),
            $this->lines('text', 'Text beside the heading (desktop)', 'Every service is done in-house, so your brand, ads, website and shoots all speak with one voice.', 300),
            $this->text('groups', 'Service groups (slugs, comma separated; empty = all)', null, 200, false),
            $this->text('kicker', 'Card label ({count} = number of services)', '{count} SERVICES', 40),
            $this->text('link_label', 'Card footer', 'See it in detail', 40),
            $this->text('card_label', 'Card name for screen readers ({group} = group name)', 'See all {group} services', 80, false),
            $this->text('jump_to', 'Cards jump to (anchor of the Service Deep Dive)', '#s-deep', 40, false),
            $this->text('hint', 'Hint under the cards', 'Tap a card to jump to its services.', 80),
            $this->image('mark', 'Card back', 'ekdanta/e-mark'),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => ['link_label' => 'See in detail']];
    }
}
