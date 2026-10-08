<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Footer;

use App\Services\SectionTypes\SectionType;

class NewsletterSection extends SectionType
{
    public function key(): string
    {
        return 'newsletter';
    }

    public function label(): string
    {
        return 'Newsletter Signup';
    }

    public function icon(): string
    {
        return 'far fa-envelope-open';
    }

    public function allowedAreas(): array
    {
        return ['footer', 'page'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'device' => true, 'rules' => ['nullable', 'string', 'max:500']],
            ['key' => 'placeholder', 'label' => 'Input Placeholder', 'type' => 'text', 'default' => 'Email', 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'button_label', 'label' => 'Button Label', 'type' => 'text', 'default' => 'Submit', 'rules' => ['nullable', 'string', 'max:50']],
            ['key' => 'success_message', 'label' => 'Success Message', 'type' => 'text', 'default' => 'Thank you for subscribing!', 'rules' => ['nullable', 'string', 'max:191']],
        ];
    }
}
