<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * About: our story. The two founders meet, then five beats hold one after
 * another: the start, years of experience, owners guided, the realisation
 * and what it became (design: About a-story).
 */
class OurStorySection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'our-story';
    }

    protected function anchor(): string
    {
        return 'a-story';
    }

    public function label(): string
    {
        return 'Ekdanta · Our Story';
    }

    protected function content(): array
    {
        $founder = fn (int $n, string $name) => [
            $this->text("founder_{$n}_name", "Founder {$n}: name", $name, 60, false),
            $this->text("founder_{$n}_role", "Founder {$n}: role", 'Founder', 40, false),
            $this->image("founder_{$n}_photo", "Founder {$n}: photo", 'ekdanta/founder-'.\Illuminate\Support\Str::slug($name)),
            $this->text("founder_{$n}_placeholder", "Founder {$n}: text shown until a photo is added", "[Photo: {$name}]", 80, false),
        ];

        return [
            ...$founder(1, 'Vaishali Hirpara'),
            ...$founder(2, 'Dishant Bhagat'),
            $this->image('mark', 'Badge where they meet', 'ekdanta/e-mark'),
            $this->text('eyebrow', '1. Small line above the heading', 'OUR STORY', 40),
            $this->lines('heading', '1. Heading (a new line breaks it)', "Ek realisation se\nshuru hua.", 120),
            $this->lines('text', '1. Text', '“We met as co-professionals.” Two founders, and one thing they kept seeing in business after business.', 300),
            $this->number('years_number', '2. Number (counts up)', 10, 0, 1000),
            $this->text('years_suffix', '2. After the number', '+', 5, false),
            $this->lines('years_text', '2. Text (a new line breaks it)', "years in sales, marketing\nand consulting.", 120),
            $this->number('owners_number', '3. Number (counts up)', 10000, 0, 10000000),
            $this->text('owners_suffix', '3. After the number', '+', 5, false),
            $this->text('owners_text', '3. Text', 'business owners guided.', 120),
            $this->text('dots_caption', '3. Under the hundred dots', 'Each dot is a hundred business owners.', 120),
            $this->lines('quote', '4. Quote', "Great businesses weren't losing because of their potential. They were losing because the digital world never saw their", 400),
            $this->text('quote_highlight', '4. Quote: highlighted end', 'true value.', 60),
            $this->text('became', '5. Line above the logo', 'That realisation became', 80),
            $this->image('logo', '5. Logo', 'ekdanta/logo-white'),
            $this->text('logo_alt', '5. Logo description', 'Ekdanta — Your Branding Partner', 80, false),
            $this->lines('founders_quote', '5. Founders’ quote', '“Every business has exceptional products. They just need the right partner to bring that value forward.”', 300),
            $this->text('founders_credit', '5. Quote credit', 'Vaishali Hirpara and Dishant Bhagat, founders', 120),
        ];
    }
}
