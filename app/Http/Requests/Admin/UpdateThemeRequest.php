<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('theme.manage') ?? false;
    }

    /**
     * Rules come from the theme schema (config/theme.php) so the form,
     * validation and storage always agree. Typography has its own nested
     * shape and is validated by rulesForTypography() in the controller.
     */
    public function rules(): array
    {
        $group = (string) $this->route('group');
        $rules = [];

        foreach ((array) config("theme.{$group}.fields", []) as $key => $field) {
            $rules[$key] = $this->rulesFor($field);
        }

        return $rules;
    }

    private function rulesFor(array $field): array
    {
        if (! empty($field['rules'])) {
            return $field['rules'];
        }

        return match ($field['type'] ?? 'color') {
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'number' => ['nullable', 'numeric'],
            'toggle' => ['nullable', 'boolean'],
            'select' => ['nullable', 'string', 'max:30'],
            'code' => ['nullable', 'string', 'max:20000'],
            default => ['nullable', 'string', 'max:500'],
        };
    }

    public function attributes(): array
    {
        $group = (string) $this->route('group');

        return collect((array) config("theme.{$group}.fields", []))
            ->map(fn (array $field) => $field['label'] ?? null)
            ->filter()
            ->all();
    }
}
