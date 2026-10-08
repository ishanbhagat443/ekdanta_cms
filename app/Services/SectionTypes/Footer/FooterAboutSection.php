<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Footer;

use App\Services\SectionTypes\SectionType;

class FooterAboutSection extends SectionType
{
    public function key(): string
    {
        return 'footer-about';
    }

    public function label(): string
    {
        return 'Footer About (logo + text)';
    }

    public function icon(): string
    {
        return 'fas fa-info-circle';
    }

    public function allowedAreas(): array
    {
        return ['footer'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'media_id', 'label' => 'Footer Logo', 'type' => 'media', 'device' => true, 'rules' => ['nullable', 'integer', 'exists:media,id']],
            ['key' => 'logo_max_height', 'label' => 'Logo Max Height (px)', 'type' => 'number', 'default' => 64, 'rules' => ['nullable', 'integer', 'between:16,300']],
            ['key' => 'text', 'label' => 'About Text', 'type' => 'textarea', 'device' => true, 'rules' => ['nullable', 'string', 'max:2000']],
        ];
    }
}
