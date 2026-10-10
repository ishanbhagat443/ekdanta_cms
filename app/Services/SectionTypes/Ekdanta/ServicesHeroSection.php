<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Services: the service rings orbit the Ekdanta mark on their own; scrolling
 * pushes the rings outwards and fades the copy (design: Services s-top).
 */
class ServicesHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'services-hero';
    }

    protected function anchor(): string
    {
        return 's-top';
    }

    public function label(): string
    {
        return 'Ekdanta · Services Hero (Orbit)';
    }

    protected function content(): array
    {
        return [
            $this->text('crumb_home', 'Breadcrumb: first link', 'Home', 40),
            $this->link('crumb_home_link', 'Breadcrumb: first link goes to', '/'),
            $this->text('crumb_current', 'Breadcrumb: this page', 'Services', 60),
            $this->text('heading_1', 'Heading, line 1', 'Ek partner.', 40),
            $this->text('heading_2', 'Heading, line 2 (green)', 'Poora kaam.', 40),
            $this->text('roll_before', 'Rolling line: text before the words', 'We handle your', 60),
            $this->lines('roll_words', 'Rolling words (one per line; the roll is timed for six, for another number pick "Word roller" in the Motion tab)', "branding\nreels\nMeta ads\nwebsite\nproduct shoots\nsales funnel", 300),
            $this->lines('text', 'Text', 'Branding, social media, ads, websites, films and sales systems, all from one in-house team in Surat. No juggling five agencies.', 400),
            $this->text('button_label', 'First button', 'Explore all services', 40),
            $this->link('button_link', 'First button link', '#s-menu'),
            $this->text('button_2_label', 'Second button', 'How pricing works', 40),
            $this->link('button_2_link', 'Second button link', '#s-price'),
            $this->text('hint', 'Scroll hint (desktop)', 'SCROLL TO EXPLORE', 60),
            $this->image('mark', 'Mark in the middle', 'ekdanta/e-mark'),
            $this->rows('inner_ring', 'Inner ring (big pills)', [
                $this->text('label', 'Label', null, 30),
                $this->colour('colour', 'Pill colour'),
            ], [
                ['label' => 'Branding', 'colour' => '#2EB45D'],
                ['label' => 'Digital', 'colour' => '#FFFFFF'],
                ['label' => 'Growth', 'colour' => '#F2A230'],
                ['label' => 'Films', 'colour' => '#8FE3AE'],
            ], 6),
            $this->lines('outer_ring', 'Outer ring (small pills, one per line)', "Brand identity\nSocial media\nMeta ads\nPackaging design\nWebsites\nLead nurturing\nProduct shoots\nSEO\nBrand films\nCRM and apps", 600, false),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => [
            'text' => 'Branding, social media, ads, websites, films and sales systems, all from one in-house team in Surat.',
            'button_label' => 'Explore services',
        ]];
    }
}
