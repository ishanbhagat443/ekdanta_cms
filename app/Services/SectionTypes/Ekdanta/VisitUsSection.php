<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Contact: the office. An illustrated map of Surat draws itself as the
 * visitor scrolls; the route runs and the pin drops (design: Contact c-visit).
 */
class VisitUsSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'visit-us';
    }

    protected function anchor(): string
    {
        return 'c-visit';
    }

    public function label(): string
    {
        return 'Ekdanta · Visit Us (Map)';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'VISIT US', 40),
            $this->text('heading', 'Heading', 'Come say namaste.', 80),
            $this->lines('address', 'Address (one line each)', "Block B, 402–403, Bhatar Trade Center\nNear Bhatar Char Rasta, Uma Bhawan Society\nBhatar, Surat, Gujarat 395017", 400),
            $this->lines('details', 'Details under the address (one line each)', "Office hours: [Add office hours]\nAlso in Ahmedabad: [Add Ahmedabad address]", 400),
            $this->text('directions_label', 'Directions button', 'Get directions', 40),
            $this->link('directions_link', 'Directions button link', 'office:1'),
            $this->text('call_label', 'Call button', 'Call before you visit', 40),
            $this->link('call_link', 'Call button link', 'phone:1'),
            $this->text('map_description', 'Map description (for screen readers)', 'Illustrative map of Surat showing the Ekdanta office near Bhatar Char Rasta', 200, false),
            $this->text('map_city', 'Map: city name', 'Surat', 30, false),
            $this->text('map_river', 'Map: river', 'Tapi River', 30, false),
            $this->text('map_junction', 'Map: junction', 'Bhatar Char Rasta', 40, false),
            $this->text('map_you', 'Map: start of the route', 'You', 20, false),
            $this->text('map_pin', 'Map: pin label', 'Ekdanta', 30, false),
            $this->text('map_place', 'Map: pin address', 'Bhatar Trade Center', 40, false),
            $this->text('map_note', 'Map: small print', 'Illustrative map. Use Get directions for the exact route.', 120, false),
            $this->image('mark', 'Map: picture in the pin', 'ekdanta/e-mark'),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => ['call_label' => 'Call first']];
    }
}
