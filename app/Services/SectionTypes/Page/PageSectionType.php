<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

use App\Services\SectionTypes\SectionType;

/**
 * Base for page-area blocks: shared field builders keep the 20 types
 * declarative and consistent.
 */
abstract class PageSectionType extends SectionType
{
    public function allowedAreas(): array
    {
        return ['page'];
    }

    protected function headingFields(bool $withEyebrow = false): array
    {
        $fields = [];

        if ($withEyebrow) {
            $fields[] = ['key' => 'eyebrow', 'label' => 'Eyebrow (small label above heading)', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:150']];
        }

        $fields[] = ['key' => 'heading', 'label' => 'Heading', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:300']];
        $fields[] = ['key' => 'subheading', 'label' => 'Subheading', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:500']];

        return $fields;
    }

    protected function richTextField(string $key = 'body', string $label = 'Content'): array
    {
        return ['key' => $key, 'label' => $label, 'type' => 'richtext', 'device' => true, 'rules' => ['nullable', 'string', 'max:65000']];
    }

    protected function buttonsRepeater(int $max = 3): array
    {
        return ['key' => 'buttons', 'label' => 'Buttons', 'type' => 'repeater', 'rules' => ["max:{$max}"], 'fields' => [
            ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'rules' => ['required', 'string', 'max:60']],
            ['key' => 'url', 'label' => 'URL', 'type' => 'text', 'rules' => ['required', 'string', 'max:500']],
            // Legacy values (gradient/outline/solid) or any Button Builder
            // style slug — resolved by frontend/partials/buttons.blade.php.
            ['key' => 'style', 'label' => 'Style (gradient/outline/solid or a Button Builder slug)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120']],
        ]];
    }

    /** Button Builder style slug for a section's single CTA button. */
    protected function buttonStyleField(string $key = 'button_style'): array
    {
        return ['key' => $key, 'label' => 'Button Style (Button Builder slug — empty = theme default)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120']];
    }

    protected function alignmentField(): array
    {
        return ['key' => 'alignment', 'label' => 'Alignment', 'type' => 'select', 'default' => 'center', 'options' => ['start' => 'Left', 'center' => 'Center', 'end' => 'Right'], 'rules' => ['nullable', 'in:start,center,end']];
    }

    protected function mediaField(string $key = 'media_id', string $label = 'Image', bool $device = true): array
    {
        return ['key' => $key, 'label' => $label, 'type' => 'media', 'device' => $device, 'rules' => ['nullable', 'integer', 'exists:media,id']];
    }
}
