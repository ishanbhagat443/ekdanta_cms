<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Footer;

use App\Services\SectionTypes\SectionType;

class FooterContactSection extends SectionType
{
    public function key(): string
    {
        return 'footer-contact';
    }

    public function label(): string
    {
        return 'Contact Details';
    }

    public function icon(): string
    {
        return 'fas fa-address-card';
    }

    public function allowedAreas(): array
    {
        return ['footer'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'title', 'label' => 'Column Title', 'type' => 'text', 'default' => 'Contact Us', 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'show_address', 'label' => 'Show Address', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'show_email', 'label' => 'Show Email', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'show_phone', 'label' => 'Show Phone', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'show_whatsapp', 'label' => 'Show WhatsApp', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'show_hours', 'label' => 'Show Working Hours', 'type' => 'toggle', 'default' => false, 'rules' => ['nullable', 'boolean']],
            ['key' => 'address_override', 'label' => 'Address Override (blank = Business Info)', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:500']],
        ];
    }
}
