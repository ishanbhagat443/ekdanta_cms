<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Case study template: the result. Number cards flip down, the client's
 * quote swings in, then a call to action (design: Case-Study c-result).
 * Reads the case being shown; on any other page, the case named below.
 */
class CaseResultsSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'case-results';
    }

    protected function anchor(): string
    {
        return 'c-result';
    }

    public function label(): string
    {
        return 'Ekdanta · Case Results';
    }

    protected function content(): array
    {
        return [
            $this->text('case', 'Case study to show when this is not the case-study template (slug)', 'trilok-infratech', 120, false),
            $this->text('eyebrow', 'Small line above the heading', 'THE RESULT', 40),
            $this->text('heading', 'Heading ({name} = the brand’s short name)', 'What changed for {name}.', 120),
            $this->text('logo_alt', 'Logo description ({name} = the brand)', '{name} logo', 80, false),
            $this->text('cta_text', 'Bottom band: text', 'Want results like this for your brand?', 120),
            $this->text('cta_button', 'Bottom band: button', 'Build your brief', 40),
            $this->link('cta_link', 'Bottom band: button link', '/contact'),
        ];
    }
}
