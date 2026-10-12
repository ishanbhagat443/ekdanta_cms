<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Work: the case studies (Collections → Case studies). An index on the
 * left, the Instagram grid in a parallax window, the story on the right;
 * each case holds while the background turns to its colour (design: Work
 * w-cases).
 */
class CaseStudiesScrollerSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'case-studies-scroller';
    }

    protected function anchor(): string
    {
        return 'w-cases';
    }

    public function label(): string
    {
        return 'Ekdanta · Case Studies Scroller';
    }

    protected function content(): array
    {
        return [
            $this->text('cases', 'Case studies (slugs, comma separated; empty = all)', 'trilok-infratech, yantra-design, heaven-solar, srk-dairy-farm-and-foods, her-and-he-cosmetics, valleyrides', 400, false),
            $this->text('eyebrow', 'Small line ({n} = this case, {total} = how many)', 'CASE STUDIES · {n} / {total}', 60),
            $this->text('window_label', 'Label on the picture ({n} = this case)', 'INSTAGRAM GRID · {n}', 40, false),
            $this->text('image_alt', 'Picture description ({name} = the brand)', 'Social media work for {name}', 120, false),
            $this->text('made_label', 'Above the list of what we made', 'WHAT WE MADE', 40),
            $this->text('result_label', 'Before the result', 'RESULT', 20),
            $this->text('button_label', 'Button (opens the case study)', 'Read the full case study', 40),
        ];
    }
}
