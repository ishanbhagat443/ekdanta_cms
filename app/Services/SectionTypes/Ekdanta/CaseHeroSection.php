<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Case study template: the hero. The brand's colour, its name and story,
 * and an Instagram profile card whose grid pans (design: Case-Study c-top).
 * Reads the case being shown (the page marked "Template for: Case study");
 * on any other page, the case named below.
 */
class CaseHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'case-hero';
    }

    protected function anchor(): string
    {
        return 'c-top';
    }

    public function label(): string
    {
        return 'Ekdanta · Case Hero';
    }

    protected function content(): array
    {
        return [
            $this->text('case', 'Case study to show when this is not the case-study template (slug)', 'trilok-infratech', 120, false),
            $this->text('crumb_home', 'Breadcrumb: first link', 'Work', 40),
            $this->link('crumb_home_link', 'Breadcrumb: first link goes to', '/work'),
            $this->text('crumb_current', 'Breadcrumb: this page ({n} = this case, {total} = how many)', 'Case study {n} of {total}', 60),
            $this->text('industry_label', 'Label: industry', 'INDUSTRY', 30),
            $this->text('made_label', 'Label: what we made', 'WHAT WE MADE', 30),
            $this->text('button_label', 'Main button', 'See the work', 40),
            $this->link('button_link', 'Main button link', '#c-gallery'),
            $this->text('button_2_label', 'Second button (with a back arrow)', 'All case studies', 40),
            $this->link('button_2_link', 'Second button link', '/work'),
            $this->text('card_line', 'Profile card: line under the name', 'Social media by Ekdanta', 60, false),
            $this->text('card_follow', 'Profile card: button', 'Follow', 20, false),
            $this->text('image_alt', 'Grid picture description ({name} = the brand)', 'Instagram grid made by Ekdanta for {name}', 120, false),
            $this->text('prev_label', 'Previous case: name read by screen readers', 'Previous case study', 60, false),
            $this->text('next_label', 'Next case: name read by screen readers', 'Next case study', 60, false),
            $this->text('hint', 'Scroll hint', 'SCROLL FOR THE STORY', 60),
        ];
    }
}
