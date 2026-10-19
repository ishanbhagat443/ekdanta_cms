<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/** Home: six obstacles flip into what Ekdanta does about them (design: j-obstacles / m-obstacles). */
class ObstaclesSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'obstacles';
    }

    protected function anchor(): string
    {
        return 'j-obstacles';
    }

    public function label(): string
    {
        return 'Ekdanta · Obstacles (Flip Cards)';
    }

    protected function content(): array
    {
        return [
            $this->lines('heading', 'Heading', "Every business has obstacles.\nRemoving them is our name.", 200),
            $this->text('counter_label', 'Counter label', 'OBSTACLES REMOVED', 60),
            $this->rows('cards', 'Cards (front: the obstacle; back: the fix)', [
                $this->text('obstacle', 'Obstacle', null, 80),
                $this->text('fix', 'Fix', null, 80),
                $this->text('fix_text', 'Fix: one line', null, 160),
            ], [
                ['obstacle' => 'Nobody knows our brand', 'fix' => 'Branding and positioning', 'fix_text' => 'Identity, packaging and a clear place in your market.'],
                ['obstacle' => 'Our posts get no reach', 'fix' => 'Social media and reels', 'fix_text' => 'Content calendars, reels and creatives that get seen.'],
                ['obstacle' => "Enquiries come, sales don't", 'fix' => 'Lead nurturing and CRM', 'fix_text' => 'Funnels and follow-ups that turn enquiries into orders.'],
                ['obstacle' => 'Our website looks outdated', 'fix' => 'Website development', 'fix_text' => 'Fast, modern websites, built and maintained in-house.'],
                ['obstacle' => 'Ads are burning money', 'fix' => 'Meta ads and performance', 'fix_text' => 'Campaigns tracked to leads and sales, not just clicks.'],
                ['obstacle' => 'No time to shoot content', 'fix' => 'Films and shoots', 'fix_text' => 'Our crew shoots at your factory, store or site.'],
            ], 6),
            $this->image('walker_image', 'Mark walking along the track', 'ekdanta/e-mark'),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => ['heading' => 'Every business has obstacles. Removing them is our name.']];
    }

    /** Screen fit: parts its automatic rule places wrongly (see SectionType::fitParts). */
    public function fitParts(): array
    {
        return [
            '[data-m="walker"]' => 'C',
        ];
    }
}
