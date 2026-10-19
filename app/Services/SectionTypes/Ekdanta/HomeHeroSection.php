<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/** Home: the elephant's torch lights up the headline (design: Final-Home j-top, M-Home m-top). */
class HomeHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'home-hero';
    }

    protected function anchor(): string
    {
        return 'j-top';
    }

    public function label(): string
    {
        return 'Ekdanta · Home Hero (Torch)';
    }

    protected function content(): array
    {
        return [
            $this->text('intro', 'Line above the heading', 'Aapki branding & marketing ke'),
            $this->text('heading', 'Heading', 'विघ्नहर्ता', 120),
            $this->lines('text', 'Text', 'We turn the obstacles in your business into रिद्धि-सिद्धि: more leads, more sales and a brand people remember.', 400),
            $this->lines('chips', 'Chips (one per line)', "Brand positioning\nCreative production\nLead nurturing\nSales funnel optimisation", 400),
            $this->text('button_label', 'Main button', 'Become a big brand', 60),
            $this->link('button_link', 'Main button link', '#contact'),
            $this->text('whatsapp_label', 'WhatsApp button', 'WhatsApp {whatsapp}', 60),
            $this->link('whatsapp_link', 'WhatsApp button link', 'wa:'),
            $this->text('hint', 'Scroll hint', 'SCROLL TO EXPLORE', 60),
            $this->image('head_image', 'Elephant head', 'ekdanta/elephant-head'),
            $this->image('trunk_image', 'Trunk with torch (moves)', 'ekdanta/elephant-trunk-torch'),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => ['whatsapp_label' => 'WhatsApp']];
    }

    /** Screen fit: parts its automatic rule places wrongly (see SectionType::fitParts). */
    public function fitParts(): array
    {
        return [
            '[data-m="beam"]' => 'S',
        ];
    }
}
