<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Work: the client wall (Collections → Clients). The logos cascade in; the
 * industry filter re-sorts the wall (design: Work w-clients).
 */
class ClientLogoWallSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'client-logo-wall';
    }

    protected function anchor(): string
    {
        return 'w-clients';
    }

    public function label(): string
    {
        return 'Ekdanta · Client Logo Wall';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'OUR CLIENTS', 40),
            $this->text('heading', 'Heading', 'Brands that trust Ekdanta.', 80),
            $this->text('clients', 'Logos (slugs from Clients, comma separated; empty = all)', '', 2000, false),
            $this->text('filter_label', 'Filter: name read by screen readers', 'Filter by industry', 60, false),
            $this->text('all_label', 'Filter: first chip', 'All', 30),
            $this->text('showing', 'Count ({n} = shown, {total} = all)', 'Showing {n} of {total}', 60),
            $this->text('footer', 'Line under the wall', 'See their stories on Instagram', 80),
            $this->text('footer_link_label', 'Its link text', '@ekdanta.in', 40),
            $this->link('footer_link', 'Its link goes to', 'https://www.instagram.com/ekdanta.in/'),
        ];
    }
}
