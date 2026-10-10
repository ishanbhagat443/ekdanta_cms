<?php

declare(strict_types=1);

namespace App\Collections;

/** The four expertise groups (Branding, Digital, Growth, Films). Their services point at them by slug. */
class ServiceGroups extends CollectionType
{
    public function key(): string { return 'service-groups'; }
    public function label(): string { return 'Service groups'; }
    public function singular(): string { return 'Service group'; }
    public function titleLabel(): string { return 'Title'; }
    public function icon(): string { return 'fas fa-th-large'; }
    public function hint(): string { return 'Order here is the order on Home (services panels) and Services.'; }

    public function fields(): array
    {
        return [
            $this->text('word', 'Big word (capitals, e.g. BRANDING)', null, 40),
            $this->text('hinglish', 'Hinglish line', null, 200),
            $this->lines('description', 'Description (Home)', null, 400),
            $this->lines('short_description', 'Short description (Services)', null, 300),
            $this->colour('colour', 'Panel colour'),
            $this->colour('text_colour', 'Text colour'),
            $this->colour('accent', 'Accent (dots)'),
            $this->colour('stroke', 'Outline word colour', null, true),
            $this->colour('rule', 'Line colour', null, true),
            $this->colour('card_colour', 'Services page: card colour'),
            $this->colour('card_dot', 'Services page: card dots'),
            $this->colour('badge', 'Services page: number badge when lit'),
            $this->colour('badge_text', 'Services page: badge number when lit'),
            $this->colour('deep_kicker', 'Services page: small label colour'),
            $this->colour('deep_line', 'Services page: Hinglish line colour'),
            $this->colour('deep_text', 'Services page: description colour'),
        ];
    }
}
