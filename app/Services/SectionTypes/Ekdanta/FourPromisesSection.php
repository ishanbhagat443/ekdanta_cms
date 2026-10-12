<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * About: four promises. A four-sided block turns to show each promise as
 * you scroll; the tabs beside it jump to one (design: About a-values).
 */
class FourPromisesSection extends EkdantaSection
{
    public const ICONS = ['building' => 'Building', 'growth' => 'Rising line', 'sliders' => 'Sliders', 'pin' => 'Map pin'];

    public const COLOURS = ['green' => 'Green', 'ink' => 'Ink', 'orange' => 'Orange', 'deep' => 'Deep green'];

    protected function slug(): string
    {
        return 'four-promises';
    }

    protected function anchor(): string
    {
        return 'a-values';
    }

    public function label(): string
    {
        return 'Ekdanta · Four Promises';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'WHAT WE STAND FOR', 40),
            $this->text('heading', 'Heading', 'Hamare chaar vaade.', 80),
            $this->lines('text', 'Text', 'Four promises we make to every brand we work with.', 200),
            $this->rows('promises', 'Promises (one per side of the block, four at most)', [
                $this->text('title', 'Title', null, 60),
                $this->lines('text', 'Text', null, 200),
                $this->select('icon', 'Icon', self::ICONS),
                $this->select('colour', 'Colour', self::COLOURS),
            ], [
                ['title' => 'Sab kuch in-house.', 'text' => 'Strategy, design, shoots, ads and development sit in one team, so nothing waits on another agency.', 'icon' => 'building', 'colour' => 'green'],
                ['title' => 'Sales pehle, likes baad mein.', 'text' => 'With 10+ years in sales behind us, we judge marketing by enquiries, orders and revenue, not by vanity numbers.', 'icon' => 'growth', 'colour' => 'ink'],
                ['title' => 'Har plan alag.', 'text' => 'No fixed packages. Every plan is built around your goals, your market and your budget.', 'icon' => 'sliders', 'colour' => 'orange'],
                ['title' => 'Ground par, aapke saath.', 'text' => 'We shoot at your factory, store or site, because real work makes the most convincing content.', 'icon' => 'pin', 'colour' => 'deep'],
            ], 4),
            $this->text('hint', 'Hint under the block', 'Keep scrolling to turn the block.', 80),
        ];
    }
}
