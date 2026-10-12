<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Case study template: the next case. Its name slides in on its colour and
 * its grid rises (design: Case-Study c-next). The next case is the case's
 * own "Next case study" field, else the next one in the list.
 */
class NextCaseSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'next-case';
    }

    protected function anchor(): string
    {
        return 'c-next';
    }

    public function label(): string
    {
        return 'Ekdanta · Next Case';
    }

    protected function content(): array
    {
        return [
            $this->text('case', 'Case study to show when this is not the case-study template (slug)', 'trilok-infratech', 120, false),
            $this->text('eyebrow', 'Small line ({n} = the next case’s number, {total} = how many)', 'NEXT CASE STUDY · {n} / {total}', 60),
            $this->text('button_label', 'Main button (opens the next case)', 'View this case study', 40),
            $this->text('button_2_label', 'Second button', 'All case studies', 40),
            $this->link('button_2_link', 'Second button link', '/work'),
            $this->text('image_alt', 'Picture description ({name} = the brand)', 'Instagram grid made by Ekdanta for {name}', 120, false),
        ];
    }
}
