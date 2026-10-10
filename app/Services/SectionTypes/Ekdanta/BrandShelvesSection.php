<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Home: client logos pop up, then fly onto their industry shelf
 * (design: j-brands / m-brands). Logos and industries: Collections → Clients.
 */
class BrandShelvesSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'brand-shelves';
    }

    protected function anchor(): string
    {
        return 'j-brands';
    }

    public function label(): string
    {
        return 'Ekdanta · Brand Shelves';
    }

    protected function content(): array
    {
        return [
            $this->text('heading', 'Heading', '45+ brands we empower', 120),
            $this->lines('text', 'Text', 'Sorted by industry. Keep scrolling and watch each one find its place.', 300),
            $this->text('link_label', 'Link', 'See their stories on Instagram @ekdanta.in', 80),
            $this->link('link', 'Link address', 'https://www.instagram.com/ekdanta.in/'),
            $this->rows('stats', 'Numbers', [
                $this->text('value', 'Number (e.g. 10,000+)', null, 20),
                $this->text('label', 'What it counts', null, 80),
                $this->text('mobile_label', 'Shorter label on phones (optional)', null, 80),
                $this->toggle('count', 'Count up'),
            ], [
                ['value' => '10+', 'label' => 'years in sales and marketing', 'mobile_label' => '', 'count' => true],
                ['value' => '10,000+', 'label' => 'business owners guided', 'mobile_label' => 'owners guided', 'count' => true],
                ['value' => '45+', 'label' => 'brands empowered', 'mobile_label' => '', 'count' => true],
                ['value' => '2', 'label' => 'cities, Surat and Ahmedabad', 'mobile_label' => '', 'count' => false],
            ], 4),
            $this->text('clients', 'Clients to show (slugs, comma separated; empty = all, in their order)', null, 2000, false),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => ['link_label' => 'See their stories on Instagram']];
    }
}
