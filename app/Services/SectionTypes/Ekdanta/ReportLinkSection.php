<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * 404 page: report a broken link. As you scroll, the broken chain joins up
 * and the link starts working (design: Not-Found n-report).
 */
class ReportLinkSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'report-link';
    }

    protected function anchor(): string
    {
        return 'n-report';
    }

    public function label(): string
    {
        return 'Ekdanta · Report Broken Link';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'FOUND A BROKEN LINK?', 60),
            $this->text('heading', 'Heading, line 1', 'Batao,', 60),
            $this->text('heading_2', 'Heading, line 2 (green)', 'hum theek kar denge.', 80),
            $this->lines('text', 'Text', "If a link on our site, in a post or in a message brought you here, send it to us on WhatsApp and we'll fix it.", 300),
            $this->text('button_label', 'Button', 'Report it on WhatsApp', 40),
            $this->link('button_link', 'Button link', 'wa:Hi Ekdanta, I found a broken link on your website:'),
            $this->toggle('add_address', 'WhatsApp button: add the address the visitor tried to open', true),
            $this->text('picture_description', 'Picture description', 'A broken link that joins up and starts working', 120, false),
            $this->text('address_start', 'Picture: address start', 'ekdanta.in/', 40, false),
            $this->text('address_broken', 'Picture: broken address end', 'old-link', 40, false),
            $this->text('address_fixed', 'Picture: fixed address end', 'services', 40, false),
            $this->text('broken_label', 'Picture: broken tag', 'Not found', 20, false),
            $this->text('fixed_label', 'Picture: fixed tag', 'Working', 20, false),
        ];
    }
}
