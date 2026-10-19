<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Home: the four expertise groups slide past sideways, the outline word
 * drifting behind each title (design: j-services / m-services).
 * Groups and their services: Collections → Service groups, Services.
 */
class ServicesPanelsSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'services-panels';
    }

    protected function anchor(): string
    {
        return 'j-services';
    }

    public function label(): string
    {
        return 'Ekdanta · Services Panels';
    }

    protected function content(): array
    {
        return [
            $this->text('label', 'Label after the number', 'OUR EXPERTISE', 60),
            $this->text('groups', 'Groups to show (slugs, comma separated; empty = all, in their order)', null, 300, false),
        ];
    }

    /** Screen fit: parts its automatic rule places wrongly (see SectionType::fitParts). */
    public function fitParts(): array
    {
        return [
            '[data-m="svc-panels"]' => 'T',
        ];
    }
}
