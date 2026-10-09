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
    use \App\Support\FieldSchema;

    abstract public function key(): string;

    abstract public function label(): string;

    abstract public function icon(): string;

    /** @return list<string> layout areas / 'page' this block may be added to */
    abstract public function allowedAreas(): array;

    /** @return list<array<string, mixed>> */
    abstract public function fields(): array;

    /** Heading it sits under in "Add a section". */
    public function group(): string
    {
        return 'General';
    }

    /** The Blade view that draws it. */
    public function view(): string
    {
        return 'frontend.sections.'.$this->key();
    }

    /**
     * Starting tablet/mobile values for a new section, where the mobile
     * design uses different words or line breaks: ['mobile' => [key => value]].
     */
    public function deviceDefaults(): array
    {
        return [];
    }

    /**
     * The design's motion for this type, per device (see App\Support\SectionMotion
     * for the shape). The Motion tab edits patches over it; "Reset to design
     * default" goes back to it. Most types have none.
     */
    public function motionDefaults(): array
    {
        return [];
    }

    /** Names of the parts its view marks with data-m, offered in the Motion tab. */
    public function motionParts(): array
    {
        return [];
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
