<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Service detail template: the hero. The service's own colours, its
 * promise and a live scene of the work (chosen per service in Collections
 * → Services); the other services are one tap away (design:
 * Service-Detail v-top). Reads the service being shown; on any other page,
 * the service named below.
 */
class ServiceHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'service-hero';
    }

    protected function anchor(): string
    {
        return 'v-top';
    }

    public function label(): string
    {
        return 'Ekdanta · Service Hero (Live Scene)';
    }

    protected function content(): array
    {
        return [
            $this->text('service', 'Service to show when this is not the service template (slug)', 'social-media-management', 120, false),
            $this->text('crumb_home', 'Breadcrumb: first link', 'Services', 40),
            $this->link('crumb_home_link', 'Breadcrumb: first link goes to', '/services'),
            $this->text('good_for_label', 'Label before "good for"', 'GOOD FOR', 30),
            $this->text('button_label', 'Main button ({service} = the service, in a sentence)', 'Talk to us about {service}', 60),
            $this->link('button_link', 'Main button link ({service} works in a WhatsApp message)', "wa:Hi Ekdanta, I'd like to know more about {service}."),
            $this->text('button_2_label', 'Second button', 'How pricing works', 40),
            $this->link('button_2_link', 'Second button link', '#v-price'),
            $this->text('all_label', 'Label before the other services', 'ALL SERVICES', 30),
            $this->text('nav_label', 'Other services: name read by screen readers', 'Other services', 60, false),
        ];
    }
}
