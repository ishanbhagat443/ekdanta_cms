<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Service detail template: pricing and questions. What the price depends
 * on for this service, and its questions (Collections → FAQs, grouped by
 * the service's slug) as an accordion (design: Service-Detail v-price).
 */
class ServicePricingFaqSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'service-pricing-faq';
    }

    protected function anchor(): string
    {
        return 'v-price';
    }

    public function label(): string
    {
        return 'Ekdanta · Service Pricing + FAQ';
    }

    protected function content(): array
    {
        return [
            $this->text('service', 'Service to show when this is not the service template (slug)', 'social-media-management', 120, false),
            $this->text('eyebrow', 'Small line above the heading', 'PRICING', 40),
            $this->text('heading', 'Heading', 'No fixed packages.', 80),
            $this->lines('text', 'Text ({service} = the service, in a sentence)', 'After a free consultation, we send one clear monthly quote. For {service}, it depends on:', 300),
            $this->text('button_label', 'Button ({service})', 'Get a quote for {service}', 60),
            $this->link('button_link', 'Button link ({service} works in a WhatsApp message)', "wa:Hi Ekdanta, I'd like to know more about {service}."),
            $this->text('faq_label', 'Small line above the questions', 'QUESTIONS', 40),
        ];
    }
}
