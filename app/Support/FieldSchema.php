<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Media;

/**
 * A field schema (section types and collections): fields() lists them,
 * this turns them into defaults and validation rules, and offers builders
 * so a schema reads like a list of what the editor sees.
 *
 * Field keys: key, label, type (text|textarea|richtext|code|toggle|media|link|
 * menu|select|repeater|number|color), default, device (overridable per
 * device), rules, options (select), fields (repeater), hint.
 */
trait FieldSchema
{
    /** @return list<array<string, mixed>> */
    abstract public function fields(): array;

    /** Starting values for a new item. */
    public function defaults(): array
    {
        $defaults = [];

        foreach ($this->fields() as $field) {
            $defaults[$field['key']] = $field['default'] ?? ($field['type'] === 'repeater' ? [] : null);
        }

        return $defaults;
    }

    /** Validation rules for a full payload. */
    public function rules(string $prefix = 'data'): array
    {
        $rules = [];

        foreach ($this->fields() as $field) {
            $rules["{$prefix}.{$field['key']}"] = $field['rules'] ?? ['nullable'];

            if (($field['type'] ?? null) === 'repeater') {
                $rules["{$prefix}.{$field['key']}"] = array_merge(['nullable', 'array'], $field['rules'] ?? []);
                foreach ($field['fields'] ?? [] as $sub) {
                    $rules["{$prefix}.{$field['key']}.*.{$sub['key']}"] = $sub['rules'] ?? ['nullable'];
                }
            }
        }

        return $rules;
    }

    /* ── Builders: key, the label the editor sees, the default ─────────── */

    protected function text(string $key, string $label, ?string $default = null, int $max = 300, bool $device = true): array
    {
        return ['key' => $key, 'label' => $label, 'type' => 'text', 'default' => $default, 'device' => $device, 'rules' => ['nullable', 'string', 'max:'.$max]];
    }

    /** Several lines; line breaks are kept on the page. */
    protected function lines(string $key, string $label, ?string $default = null, int $max = 2000, bool $device = true): array
    {
        return ['key' => $key, 'label' => $label, 'type' => 'textarea', 'default' => $default, 'device' => $device, 'rules' => ['nullable', 'string', 'max:'.$max]];
    }

    protected function link(string $key, string $label, ?string $default = null, bool $device = false): array
    {
        return ['key' => $key, 'label' => $label, 'type' => 'link', 'default' => $default, 'device' => $device, 'rules' => Link::rules(), 'hint' => Link::HINT];
    }

    /** An image from the Media Library; a default is its stable key ("ekdanta/logo-colour"). */
    protected function image(string $key, string $label, ?string $default = null, bool $device = false): array
    {
        return ['key' => $key, 'label' => $label, 'type' => 'media', 'default' => $default, 'device' => $device, 'rules' => self::mediaRules()];
    }

    protected function number(string $key, string $label, int|float|null $default = null, int $min = 0, int $max = 100000): array
    {
        return ['key' => $key, 'label' => $label, 'type' => 'number', 'default' => $default, 'device' => false, 'rules' => ['nullable', 'numeric', "between:{$min},{$max}"]];
    }

    /** A colour; $alpha allows rgba(…) too (typed, as a colour picker can't hold transparency). */
    protected function colour(string $key, string $label, ?string $default = null, bool $alpha = false): array
    {
        return $alpha
            ? ['key' => $key, 'label' => $label.' (#hex or rgba)', 'type' => 'text', 'default' => $default, 'device' => false,
                'rules' => ['nullable', 'regex:/^(#[0-9a-fA-F]{6}|rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+)\s*)?\))$/']]
            : ['key' => $key, 'label' => $label, 'type' => 'color', 'default' => $default, 'device' => false, 'rules' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/']];
    }

    protected function select(string $key, string $label, array $options, ?string $default = null): array
    {
        return ['key' => $key, 'label' => $label, 'type' => 'select', 'options' => $options, 'default' => $default ?? array_key_first($options), 'device' => false,
            'rules' => ['nullable', 'in:'.implode(',', array_keys($options))]];
    }

    protected function toggle(string $key, string $label, bool $default = false): array
    {
        return ['key' => $key, 'label' => $label, 'type' => 'toggle', 'default' => $default, 'device' => false, 'rules' => ['nullable', 'boolean']];
    }

    /** A list of rows; $fields use the same builders, $default holds the design's rows. */
    protected function rows(string $key, string $label, array $fields, array $default = [], int $max = 30): array
    {
        return ['key' => $key, 'label' => $label, 'type' => 'repeater', 'default' => $default, 'device' => false, 'rules' => ['max:'.$max],
            'fields' => array_map(fn ($f) => array_diff_key($f, ['default' => true, 'device' => true, 'hint' => true]), $fields)];
    }

    /** An id or a stable key that exists in the Media Library. */
    public static function mediaRules(): array
    {
        return ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }
            $found = Media::isKey($value)
                ? Media::query()->where('key', $value)->exists()
                : is_numeric($value) && Media::query()->whereKey((int) $value)->exists();
            if (! $found) {
                $fail('Choose an image from the Media Library.');
            }
        }];
    }
}
