<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    /**
     * Rules come from the settings schema (config/settings.php) so the
     * form, validation and storage always agree.
     */
    public function rules(): array
    {
        $group = (string) $this->route('group');
        $rules = [];

        foreach ((array) config("settings.{$group}.fields", []) as $key => $field) {
            $rules[$key] = $field['rules'] ?? ['nullable', 'string'];

            // A list's rows are validated field by field.
            foreach ((array) ($field['fields'] ?? []) as $sub) {
                $rules["{$key}.*.{$sub['key']}"] = $sub['rules'] ?? ['nullable', 'string'];
            }
        }

        $rules['remove'] = ['sometimes', 'array'];
        $rules['remove.*'] = ['string'];

        return $rules;
    }

    public function attributes(): array
    {
        $group = (string) $this->route('group');

        return collect((array) config("settings.{$group}.fields", []))
            ->map(fn (array $field) => $field['label'] ?? null)
            ->filter()
            ->all();
    }
}
