<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Header;

use App\Services\SectionTypes\SectionType;

class SearchSection extends SectionType
{
    public function key(): string
    {
        return 'search';
    }

    public function label(): string
    {
        return 'Search';
    }

    public function icon(): string
    {
        return 'fas fa-search';
    }

    public function allowedAreas(): array
    {
        return ['header'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'placeholder', 'label' => 'Placeholder', 'type' => 'text', 'default' => 'Search…', 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'action_url', 'label' => 'Search Results URL', 'type' => 'text', 'default' => '/blogs', 'rules' => ['nullable', 'string', 'max:500'], 'hint' => 'Query is appended as ?search=…'],
            ['key' => 'display', 'label' => 'Display', 'type' => 'select', 'default' => 'icon', 'options' => ['icon' => 'Icon → expands', 'input' => 'Always-visible input'], 'rules' => ['nullable', 'in:icon,input']],
        ];
    }
}
