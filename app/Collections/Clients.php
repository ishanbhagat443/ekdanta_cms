<?php

declare(strict_types=1);

namespace App\Collections;

/** Client logos: Home brand shelves, About, Work logo wall and its industry filter. */
class Clients extends CollectionType
{
    public const INDUSTRIES = ['Manufacturing' => 'Manufacturing', 'Food and beverage' => 'Food and beverage', 'Retail' => 'Retail', 'Services' => 'Services'];

    public function key(): string { return 'clients'; }
    public function label(): string { return 'Clients'; }
    public function singular(): string { return 'Client'; }
    public function icon(): string { return 'fas fa-handshake'; }
    public function hint(): string { return 'Each logo is its own image. The industry puts it on a shelf and in the Work filter.'; }

    public function fields(): array
    {
        return [
            $this->image('logo', 'Logo'),
            $this->select('industry', 'Industry', self::INDUSTRIES),
        ];
    }
}
