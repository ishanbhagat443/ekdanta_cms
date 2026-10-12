<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Service detail template: works well with. Three other services (the
 * service's "works well with" field) rise in on their own colours; each
 * opens its page (design: Service-Detail v-related).
 */
class WorksWellWithSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'works-well-with';
    }

    protected function anchor(): string
    {
        return 'v-related';
    }

    public function label(): string
    {
        return 'Ekdanta · Works Well With';
    }

    protected function content(): array
    {
        return [
            $this->text('service', 'Service to show when this is not the service template (slug)', 'social-media-management', 120, false),
            $this->text('eyebrow', 'Small line above the heading', 'WORKS WELL WITH', 40),
            $this->text('heading', 'Heading', 'Better together.', 80),
            $this->text('link_label', 'Card link ({service} = that service, in a sentence)', 'Explore {service}', 60),
        ];
    }
}
