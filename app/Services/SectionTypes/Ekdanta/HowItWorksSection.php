<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Service detail template: how it works. The service's steps light up one
 * by one along a track (design: Service-Detail v-steps).
 */
class HowItWorksSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'how-it-works';
    }

    protected function anchor(): string
    {
        return 'v-steps';
    }

    public function label(): string
    {
        return 'Ekdanta · How It Works';
    }

    protected function content(): array
    {
        return [
            $this->text('service', 'Service to show when this is not the service template (slug)', 'social-media-management', 120, false),
            $this->text('eyebrow', 'Small line above the heading', 'HOW IT WORKS', 40),
            $this->text('heading', 'Heading', 'Four steps, start to finish.', 80),
            $this->text('step_label', 'Card label ({n} = the step)', 'STEP {n}', 20),
        ];
    }
}
