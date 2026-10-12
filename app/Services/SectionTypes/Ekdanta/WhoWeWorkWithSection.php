<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

use App\Collections\Clients;

/**
 * About: who we work with. The counts run, three rows of client logos
 * (Collections → Clients) slide past in opposite directions and the
 * industry cards fill, each counting its clients (design: About a-brands).
 */
class WhoWeWorkWithSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'who-we-work-with';
    }

    protected function anchor(): string
    {
        return 'a-brands';
    }

    public function label(): string
    {
        return 'Ekdanta · Who We Work With';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'WHO WE WORK WITH', 40),
            $this->text('brands_count', 'Heading: brands number (counts up)', '45+', 10, false),
            $this->text('brands_label', 'Heading: after it', 'brands.', 30),
            $this->text('industries_count', 'Heading: industries number', '4', 10, false),
            $this->text('industries_label', 'Heading: after it', 'industries.', 30),
            $this->text('cities_count', 'Heading: cities number', '2', 10, false),
            $this->text('cities_label', 'Heading: after it', 'cities.', 30),
            $this->lines('text', 'Text', 'From precast infrastructure and CNC machines to ghee, skincare and Himalayan treks.', 300),
            $this->text('clients', 'Logos (slugs from Clients, comma separated; empty = all)', '', 2000, false),
            $this->rows('industries', 'Industry cards (each counts its clients)', [
                $this->select('industry', 'Industry', Clients::INDUSTRIES),
                $this->text('examples', 'Examples', null, 160),
            ], [
                ['industry' => 'Manufacturing', 'examples' => 'Trilok Infratech, Yantra Design, Heaven Solar'],
                ['industry' => 'Food and beverage', 'examples' => 'SRK Dairy, Raj Wafers, Jenish Spices'],
                ['industry' => 'Retail', 'examples' => 'Vijay Mobile, Her & He Cosmetics, Karvi Realtors'],
                ['industry' => 'Services', 'examples' => 'ValleyRides, Zorko, MoneyMatters AM'],
            ], 4),
        ];
    }
}
