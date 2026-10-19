<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

use App\Support\LegalContent;

/**
 * Privacy Policy and Terms of Service: the hero (design: Privacy l-top,
 * Terms t-top). The heading rises line by line, then the picture builds
 * itself: the brief card with a shield and lock, or the agreement being
 * signed and stamped. Scrolling lifts the text away and drifts the chips.
 * The reading time is worked out from the page's Legal Body.
 */
class LegalHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'legal-hero';
    }

    protected function anchor(): string
    {
        return 'l-top';
    }

    public function label(): string
    {
        return 'Ekdanta · Legal Hero';
    }

    protected function content(): array
    {
        $d = LegalContent::sections('privacy-policy')['ekdanta-legal-hero'];

        return [
            $this->text('eyebrow', 'Small line above the heading', $d['eyebrow'], 60),
            $this->text('heading_1', 'Heading, line 1', $d['heading_1'], 60),
            $this->text('heading_2', 'Heading, line 2 (accent colour)', $d['heading_2'], 60),
            $this->lines('text', 'Text', $d['text'], 400),
            $this->text('date', '"Last updated" date', $d['date'], 40, false),
            $this->text('meta', 'Date line ({date}; {minutes} = reading time, worked out from the page text)', $d['meta'], 120),
            $this->text('button_1_label', 'Button 1 (filled)', $d['button_1_label'], 40),
            $this->link('button_1_link', 'Button 1 goes to', $d['button_1_link']),
            $this->text('button_2_label', 'Button 2 (outlined)', $d['button_2_label'], 40),
            $this->link('button_2_link', 'Button 2 goes to', $d['button_2_link']),
            $this->lines('chips', 'Chips on the picture (one per line, three fit)', $d['chips'], 200, false),
            $this->select('visual', 'Picture', ['privacy' => 'Privacy: brief card with a shield', 'terms' => 'Terms: agreement with signatures and a stamp'], $d['visual']),
            $this->colour('bg', 'Background', $d['bg']),
            $this->colour('fg', 'Text (the chips and dots take it too)', $d['fg']),
            $this->colour('accent', 'Accent (line 2, small line, date)', $d['accent']),
            $this->colour('sub', 'Text under the heading', $d['sub']),
        ];
    }
}
