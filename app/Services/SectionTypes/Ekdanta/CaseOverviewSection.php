<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Case study template: the brief, our approach and what we made, then
 * facts about the client (design: Case-Study c-overview). Reads the case
 * being shown; on any other page, the case named below.
 */
class CaseOverviewSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'case-overview';
    }

    protected function anchor(): string
    {
        return 'c-overview';
    }

    public function label(): string
    {
        return 'Ekdanta · Case Overview';
    }

    protected function content(): array
    {
        return [
            $this->text('case', 'Case study to show when this is not the case-study template (slug)', 'trilok-infratech', 120, false),
            $this->text('eyebrow', 'Small line above the heading', 'THE PROJECT', 40),
            $this->text('heading', 'Heading', 'The brand, the brief and the work.', 120),
            $this->text('brief_label', 'Column 1 label', 'THE BRIEF', 40),
            $this->text('approach_label', 'Column 2 label', 'OUR APPROACH', 40),
            $this->text('made_label', 'Column 3 label', 'WHAT WE MADE', 40),
            $this->text('about_label', 'Label before the client facts', 'ABOUT THE CLIENT', 40),
            $this->text('logo_alt', 'Logo description ({name} = the brand)', '{name} logo', 80, false),
        ];
    }
}
