<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class MapSection extends PageSectionType
{
    public function key(): string
    {
        return 'map';
    }

    public function label(): string
    {
        return 'Google Map';
    }

    public function icon(): string
    {
        return 'fas fa-map-marked-alt';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'use_business_map', 'label' => 'Use map from Business Info settings', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'embed_url', 'label' => 'Custom Embed URL (overrides)', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
            ['key' => 'height', 'label' => 'Height (px)', 'type' => 'number', 'default' => 400, 'rules' => ['nullable', 'integer', 'between:200,900']],
        ];
    }
}
