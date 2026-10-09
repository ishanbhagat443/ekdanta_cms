<?php

declare(strict_types=1);

namespace App\Collections;

/** The roles on the About page's team roll call. */
class TeamRoles extends CollectionType
{
    public function key(): string { return 'team-roles'; }
    public function label(): string { return 'Team roles'; }
    public function singular(): string { return 'Team role'; }
    public function titleLabel(): string { return 'Role'; }
    public function icon(): string { return 'fas fa-users'; }

    public function fields(): array
    {
        return [
            $this->text('description', 'What they do', null, 200),
        ];
    }
}
