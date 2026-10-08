<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class TeamSection extends PageSectionType
{
    public function key(): string
    {
        return 'team';
    }

    public function label(): string
    {
        return 'Team Members';
    }

    public function icon(): string
    {
        return 'fas fa-users';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'members', 'label' => 'Members', 'type' => 'repeater', 'rules' => ['max:12'], 'fields' => [
                ['key' => 'media_id', 'label' => 'Photo', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id']],
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                ['key' => 'role', 'label' => 'Role', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
                ['key' => 'text', 'label' => 'Short Text', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:300']],
                ['key' => 'social_links', 'label' => 'Social Links (optional)', 'type' => 'repeater', 'rules' => ['max:6'], 'fields' => [
                    ['key' => 'icon', 'label' => 'Icon class (fab fa-…)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60']],
                    ['key' => 'url', 'label' => 'URL', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
                ]],
            ]],
            ['key' => 'heading_ornament', 'label' => 'Diamond ornament under the heading', 'type' => 'toggle', 'default' => false, 'rules' => ['nullable', 'boolean']],
            ['key' => 'show_border', 'label' => 'Thin gold border top & bottom', 'type' => 'toggle', 'default' => false, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
