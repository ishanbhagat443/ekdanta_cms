<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/** Home: the founders' story lights up word by word and the numbers count up (design: j-about / m-about). */
class FoundersStorySection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'founders-story';
    }

    protected function anchor(): string
    {
        return 'j-about';
    }

    public function label(): string
    {
        return 'Ekdanta · Founders Story';
    }

    protected function content(): array
    {
        return [
            $this->text('intro', 'Line above the heading', 'Ekdanta ke', 80),
            $this->text('heading', 'Heading', 'कर्ता-धर्ता', 80),
            $this->lines('story', 'Story (lights up word by word)', "We met as co-professionals. Across 10+ years in sales, marketing and consulting, we've guided 10,000+ business owners, and saw that great businesses weren't losing because of their potential. They were losing because the digital world never saw their true value. That realisation became Ekdanta.", 1200),
            $this->text('signature', 'Signature', 'Vaishali Hirpara and Dishant Bhagat, founders', 160),
            $this->rows('photos', 'Founder photos', [
                $this->image('image', 'Photo'),
                $this->text('name', 'Name (photo description; shown until a photo is chosen)', null, 120),
            ], [
                ['image' => null, 'name' => '[Photo: Vaishali Hirpara]'],
                ['image' => null, 'name' => '[Photo: Dishant Bhagat]'],
            ], 2),
            $this->rows('stats', 'Numbers', [
                $this->text('value', 'Number (e.g. 10,000+)', null, 20),
                $this->text('label', 'What it counts', null, 80),
                $this->toggle('count', 'Count up'),
            ], [
                ['value' => '10+', 'label' => 'years in sales, marketing and consulting', 'count' => true],
                ['value' => '10,000+', 'label' => 'business owners guided', 'count' => true],
                ['value' => '45+', 'label' => 'brands empowered', 'count' => true],
                ['value' => '2', 'label' => 'cities: Surat and Ahmedabad', 'count' => false],
            ], 4),
        ];
    }
}
