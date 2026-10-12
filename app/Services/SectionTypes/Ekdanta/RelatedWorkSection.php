<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Service detail template: related work. The service's case studies (or
 * its placeholder cards, until there are some) slide in from the sides
 * (design: Service-Detail v-proof).
 */
class RelatedWorkSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'related-work';
    }

    protected function anchor(): string
    {
        return 'v-proof';
    }

    public function label(): string
    {
        return 'Ekdanta · Related Work';
    }

    protected function content(): array
    {
        return [
            $this->text('service', 'Service to show when this is not the service template (slug)', 'social-media-management', 120, false),
            $this->text('eyebrow', 'Small line above the heading (the heading is the service’s own)', 'RELATED WORK', 40),
            $this->text('link_label', 'Case card link', 'Read the case study', 40),
            $this->text('image_alt', 'Case picture description ({name} = the brand)', 'Social media work for {name}', 120, false),
        ];
    }
}
