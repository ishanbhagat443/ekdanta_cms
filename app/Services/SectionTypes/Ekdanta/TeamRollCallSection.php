<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * About: the team. Each role (Collections → Team roles) slides in from
 * alternate sides, then a roll call runs down the list (design: About
 * a-team).
 */
class TeamRollCallSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'team-roll-call';
    }

    protected function anchor(): string
    {
        return 'a-team';
    }

    public function label(): string
    {
        return 'Ekdanta · Team Roll Call';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'THE TEAM', 40),
            $this->text('heading', 'Heading', 'Ek chhat. Poori team.', 80),
            $this->lines('text', 'Text', 'Everything your brand needs is made by people who sit in the same office in Surat.', 300),
            $this->text('roles', 'Roles (slugs from Team roles, comma separated; empty = all, seven at most)', '', 400, false),
        ];
    }
}
