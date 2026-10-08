<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Header;

use App\Services\SectionTypes\SectionType;

class CtaButtonSection extends SectionType
{
    public function key(): string
    {
        return 'cta-button';
    }

    public function label(): string
    {
        return 'CTA Button';
    }

    public function icon(): string
    {
        return 'fas fa-hand-pointer';
    }

    public function allowedAreas(): array
    {
        return ['header', 'topbar', 'footer', 'page'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'label', 'label' => 'Button Label', 'type' => 'text', 'device' => true, 'rules' => ['required', 'string', 'max:60']],
            ['key' => 'url', 'label' => 'Button URL', 'type' => 'text', 'rules' => ['required', 'string', 'max:500']],
            ['key' => 'style', 'label' => 'Style', 'type' => 'select', 'default' => 'gradient', 'options' => ['gradient' => 'Gradient pill', 'outline' => 'Outline pill', 'solid' => 'Solid'], 'rules' => ['nullable', 'in:gradient,outline,solid']],
            ['key' => 'icon', 'label' => 'Icon class (optional)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60'], 'hint' => 'Font Awesome class, e.g. fab fa-whatsapp'],
            ['key' => 'new_tab', 'label' => 'Open in new tab', 'type' => 'toggle', 'default' => false, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
