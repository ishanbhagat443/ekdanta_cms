<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

use Illuminate\Support\Facades\Schema;

class FormSection extends PageSectionType
{
    public function key(): string
    {
        return 'form';
    }

    public function label(): string
    {
        return 'Form (Form Builder)';
    }

    public function icon(): string
    {
        return 'fas fa-clipboard-list';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'form_id', 'label' => 'Form', 'type' => 'select', 'options' => $this->formOptions(), 'rules' => ['nullable', 'integer', 'exists:forms,id']],
            ['key' => 'show_map', 'label' => 'Show map beside form (50 / 50)', 'type' => 'toggle', 'default' => false, 'rules' => ['nullable', 'boolean']],
            ['key' => 'map_embed', 'label' => 'Google Map — Embed code / iframe (blank = Business Info map)', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:4000']],
            ['key' => 'map_height', 'label' => 'Map Height (px)', 'type' => 'number', 'default' => 450, 'rules' => ['nullable', 'integer', 'between:200,900']],
            ['key' => 'map_radius', 'label' => 'Map Border Radius (px)', 'type' => 'number', 'default' => 12, 'rules' => ['nullable', 'integer', 'between:0,60']],
            ['key' => 'map_lazy', 'label' => 'Lazy-load the map', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
        ];
    }

    /** @return array<int|string, string> */
    private function formOptions(): array
    {
        if (! Schema::hasTable('forms')) {
            return [];
        }

        return \App\Models\Form::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
