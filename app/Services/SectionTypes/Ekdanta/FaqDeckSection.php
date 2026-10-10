<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Contact: a deck of question cards; scrolling throws the top card away
 * and brings the next forward (design: Contact c-faq). Questions come from
 * Collections → FAQs, by group.
 */
class FaqDeckSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'faq-deck';
    }

    protected function anchor(): string
    {
        return 'c-faq';
    }

    public function label(): string
    {
        return 'Ekdanta · FAQ Deck';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'BEFORE YOU CALL', 40),
            $this->lines('heading', 'Heading', 'Questions we hear a lot.', 120),
            $this->text('group', 'FAQ group (Collections → FAQs)', 'contact', 120, false),
            $this->text('counter', 'Counter ({n} = this question, {total} = how many)', 'Question {n} of {total}', 60),
            $this->text('previous_label', 'Previous button (for screen readers)', 'Previous question', 40, false),
            $this->text('next_label', 'Next button (for screen readers)', 'Next question', 40, false),
            $this->text('more_before', 'Line under the deck: text', 'Still curious?', 80),
            $this->text('more_link_label', 'Line under the deck: link', 'Ask us on WhatsApp', 60),
            $this->link('more_link', 'Line under the deck: link goes to', 'wa:'),
            $this->text('more_after', 'Line under the deck: after the link', '.', 20),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => ['heading' => "Questions we\nhear a lot."]];
    }
}
