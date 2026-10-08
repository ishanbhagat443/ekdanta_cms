<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Footer;

use App\Services\SectionTypes\SectionType;
use Illuminate\Support\Facades\Schema;

class FooterFormSection extends SectionType
{
    public function key(): string
    {
        return 'footer-form';
    }

    public function label(): string
    {
        return 'Footer Form (Form Builder)';
    }

    public function icon(): string
    {
        return 'fas fa-clipboard-list';
    }

    public function allowedAreas(): array
    {
        return ['footer'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'title', 'label' => 'Column Title', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'form_id', 'label' => 'Form', 'type' => 'select', 'options' => $this->formOptions(), 'rules' => ['nullable', 'integer', 'exists:forms,id']],
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
