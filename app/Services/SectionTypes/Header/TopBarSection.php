<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Header;

use App\Services\SectionTypes\SectionType;

class TopBarSection extends SectionType
{
    public function key(): string
    {
        return 'top-bar';
    }

    public function label(): string
    {
        return 'Top Bar (contact strip)';
    }

    public function icon(): string
    {
        return 'fas fa-window-maximize';
    }

    public function allowedAreas(): array
    {
        return ['topbar'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'left_text', 'label' => 'Left Text', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:191']],
            ['key' => 'left_link_label', 'label' => 'Left Link Label', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'left_link_url', 'label' => 'Left Link URL', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            ['key' => 'show_email', 'label' => 'Show Email (from Business Info)', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'show_phone', 'label' => 'Show Phone (from Business Info)', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'bg_color', 'label' => 'Background Color', 'type' => 'color', 'default' => '#111827', 'rules' => ['nullable', 'string', 'max:20']],
            ['key' => 'text_color', 'label' => 'Text Color', 'type' => 'color', 'default' => '#ffffff', 'rules' => ['nullable', 'string', 'max:20']],
        ];
    }
}
