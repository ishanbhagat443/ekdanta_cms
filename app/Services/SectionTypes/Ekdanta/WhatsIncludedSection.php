<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Service detail template: what's included. The service's items (its
 * "what's included" rows) are dealt from a stack into a grid and ticked
 * (design: Service-Detail v-incl).
 */
class WhatsIncludedSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'whats-included';
    }

    protected function anchor(): string
    {
        return 'v-incl';
    }

    public function label(): string
    {
        return "Ekdanta · What's Included";
    }

    protected function content(): array
    {
        return [
            $this->text('service', 'Service to show when this is not the service template (slug)', 'social-media-management', 120, false),
            $this->text('eyebrow', 'Small line above the heading', 'WHAT YOU GET', 40),
            $this->text('heading', 'Heading', "What's included, end to end.", 80),
        ];
    }
}
