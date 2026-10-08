<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Header;

use App\Services\SectionTypes\SectionType;

class AnnouncementBarSection extends SectionType
{
    public function key(): string
    {
        return 'announcement-bar';
    }

    public function label(): string
    {
        return 'Announcement Bar';
    }

    public function icon(): string
    {
        return 'fas fa-bullhorn';
    }

    public function allowedAreas(): array
    {
        return ['topbar', 'header'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'message', 'label' => 'Message', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:300']],
            ['key' => 'link_label', 'label' => 'Link Label', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'link_url', 'label' => 'Link URL', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            ['key' => 'dismissible', 'label' => 'Visitor can dismiss', 'type' => 'toggle', 'default' => false, 'rules' => ['nullable', 'boolean']],
            ['key' => 'bg_color', 'label' => 'Background Color', 'type' => 'color', 'default' => '#1b2a4a', 'rules' => ['nullable', 'string', 'max:20']],
            ['key' => 'text_color', 'label' => 'Text Color', 'type' => 'color', 'default' => '#ffffff', 'rules' => ['nullable', 'string', 'max:20']],
        ];
    }
}
