<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Services: one full-screen panel per service group, opened by a circle
 * wipe; its services light up one by one while their animated tiles build
 * (design: Services s-deep). Groups and services: Collections → Service
 * groups and Services (each service picks its tile artwork).
 */
class ServiceDeepDiveSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'service-deep-dive';
    }

    protected function anchor(): string
    {
        return 's-deep';
    }

    public function label(): string
    {
        return 'Ekdanta · Service Deep Dive';
    }

    protected function content(): array
    {
        return [
            $this->text('groups', 'Service groups (slugs, comma separated; empty = all)', null, 200, false),
            $this->text('kicker', 'Group label ({n} = its number, {total} = how many, {word} = its big word)', '{n} / {total} · {word}', 60),
            $this->text('nav_label', 'Group tabs (for screen readers)', 'Service groups', 60, false),
        ];
    }

    /** Screen fit: parts its automatic rule places wrongly (see SectionType::fitParts). */
    public function fitParts(): array
    {
        return [
            '.ek-comp--d [data-m="deep-groups"]' => 'S',
        ];
    }
}
