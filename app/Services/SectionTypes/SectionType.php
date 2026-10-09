<?php

declare(strict_types=1);

namespace App\Services\SectionTypes;

/**
 * Base class for every builder block. A section type declares its field
 * schema once; the admin form, validation and rendering all derive from
 * it — adding a block never touches the engine.
 *
 * Field definition keys:
 *  - key      string   payload key
 *  - label    string
 *  - type     string   text|textarea|code|toggle|media|menu|select|repeater|number|color
 *  - device   bool     true = per-device overridable (tablet/mobile inherit desktop)
 *  - rules    array    Laravel validation rules for the desktop value
 *  - options  array    for select: value => label
 *  - fields   array    for repeater: nested field definitions
 *  - hint     string   admin help text
 */
abstract class SectionType
{
    abstract public function key(): string;

    abstract public function label(): string;

    abstract public function icon(): string;

    /** @return list<string> layout areas / 'page' this block may be added to */
    abstract public function allowedAreas(): array;

    /** @return list<array<string, mixed>> */
    abstract public function fields(): array;

    /** Initial desktop payload for a fresh section. */
    public function defaults(): array
    {
        $defaults = [];

        foreach ($this->fields() as $field) {
            $defaults[$field['key']] = $field['default'] ?? ($field['type'] === 'repeater' ? [] : null);
        }

        return $defaults;
    }

    /** Validation rules for a full payload (desktop). */
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

    /** True for a section that draws its own site footer: the layout then skips the usual one. */
    public function hidesFooter(): bool
    {
        return false;
    }

    /** Keys that may be overridden per device. */
    public function deviceKeys(): array
    {
        return collect($this->fields())
            ->filter(fn (array $f) => (bool) ($f['device'] ?? false))
            ->pluck('key')
            ->all();
    }
}
