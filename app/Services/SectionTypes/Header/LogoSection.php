<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Header;

use App\Services\SectionTypes\SectionType;

class LogoSection extends SectionType
{
    public function key(): string
    {
        return 'logo';
    }

    public function label(): string
    {
        return 'Logo';
    }

    public function icon(): string
    {
        return 'fas fa-copyright';
    }

    public function allowedAreas(): array
    {
        return ['header', 'footer'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'media_id', 'label' => 'Logo Image (dark — solid header)', 'type' => 'media', 'device' => true, 'rules' => ['nullable', 'integer', 'exists:media,id'], 'hint' => 'Different logo per device is supported (e.g. compact mark on mobile).'],
            ['key' => 'light_media_id', 'label' => 'Light Logo (transparent header over hero)', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id'], 'hint' => 'Shown while the overlay header is transparent; falls back to the main logo.'],
            ['key' => 'alt', 'label' => 'Alt Text', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:191']],
            ['key' => 'link', 'label' => 'Link URL', 'type' => 'text', 'default' => '/', 'rules' => ['nullable', 'string', 'max:500']],
            ['key' => 'max_height', 'label' => 'Max Height (px)', 'type' => 'number', 'device' => true, 'default' => 56, 'rules' => ['nullable', 'integer', 'between:16,300']],
        ];
    }
}
