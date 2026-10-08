<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Footer;

use App\Services\SectionTypes\SectionType;

/**
 * Renders active networks from Settings → Social Media. No URLs stored
 * here — single source of truth stays in settings.
 */
class SocialIconsSection extends SectionType
{
    public function key(): string
    {
        return 'social-icons';
    }

    public function label(): string
    {
        return 'Social Icons';
    }

    public function icon(): string
    {
        return 'fas fa-share-alt';
    }

    public function allowedAreas(): array
    {
        return ['footer', 'topbar', 'header', 'page'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title (e.g. Follow Me)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'style', 'label' => 'Icon Style', 'type' => 'select', 'default' => 'boxed', 'options' => ['boxed' => 'Boxed squares', 'circle' => 'White circles (design)', 'plain' => 'Plain icons'], 'rules' => ['nullable', 'in:boxed,circle,plain']],
        ];
    }
}
