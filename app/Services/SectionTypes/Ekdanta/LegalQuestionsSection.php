<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

use App\Support\LegalContent;

/**
 * Privacy Policy and Terms of Service: "Questions?" (design: Privacy l-ask,
 * Terms t-ask). A paper plane flies along the dotted path to the card,
 * which swings in; its rows follow one another.
 */
class LegalQuestionsSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'legal-questions';
    }

    protected function anchor(): string
    {
        return 'l-ask';
    }

    public function label(): string
    {
        return 'Ekdanta · Legal Questions';
    }

    protected function content(): array
    {
        $d = LegalContent::sections('privacy-policy')['ekdanta-legal-questions'];

        return [
            $this->text('eyebrow', 'Small line above the heading', $d['eyebrow'], 60),
            $this->text('title', 'Heading', $d['title'], 80),
            $this->lines('text', 'Text', $d['text'], 300),
            $this->rows('rows', 'Card rows', [
                $this->text('label', 'Label', null, 40),
                $this->text('value', 'Value', null, 300),
            ], $d['rows'], 6),
            $this->text('button_1_label', 'Button 1 (filled)', $d['button_1_label'], 40),
            $this->link('button_1_link', 'Button 1 goes to', $d['button_1_link']),
            $this->text('button_2_label', 'Button 2 (outlined)', $d['button_2_label'], 40),
            $this->link('button_2_link', 'Button 2 goes to', $d['button_2_link']),
            $this->colour('bg', 'Background', $d['bg']),
        ];
    }
}
