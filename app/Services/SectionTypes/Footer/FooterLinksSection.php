<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Footer;

use App\Services\SectionTypes\SectionType;

/**
 * Reusable link-column widget. One type serves Quick Links, Important
 * Links and Policies — three instances with different titles/sources.
 */
class FooterLinksSection extends SectionType
{
    public function key(): string
    {
        return 'footer-links';
    }

    public function label(): string
    {
        return 'Link Column (Quick/Important/Policies)';
    }

    public function icon(): string
    {
        return 'fas fa-link';
    }

    public function allowedAreas(): array
    {
        return ['footer'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'title', 'label' => 'Column Title', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'source', 'label' => 'Link Source', 'type' => 'select', 'default' => 'custom', 'options' => ['custom' => 'Custom links (below)', 'menu' => 'Menu (Menus module)'], 'rules' => ['nullable', 'in:custom,menu']],
            ['key' => 'menu_id', 'label' => 'Menu', 'type' => 'menu', 'rules' => ['nullable', 'integer', 'exists:menus,id']],
            ['key' => 'links', 'label' => 'Custom Links', 'type' => 'repeater', 'rules' => ['max:20'], 'fields' => [
                ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                ['key' => 'url', 'label' => 'URL', 'type' => 'text', 'rules' => ['required', 'string', 'max:500']],
            ]],
        ];
    }
}
