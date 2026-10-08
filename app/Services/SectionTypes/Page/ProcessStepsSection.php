<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * Working-process zigzag timeline (design 05 Process.png): centered serif
 * heading + ornament, horizontal dashed line with numbered gradient badges,
 * steps alternating text-above/photo-below and photo-above/text-below.
 * Step photos render inside gold ogee-arch frames (SVG clip).
 */
class ProcessStepsSection extends PageSectionType
{
    public function key(): string
    {
        return 'process-steps';
    }

    public function label(): string
    {
        return 'Working Process (Zigzag Timeline)';
    }

    public function icon(): string
    {
        return 'fas fa-route';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'items', 'label' => 'Process Steps', 'type' => 'repeater', 'rules' => ['max:6'], 'fields' => [
                ['key' => 'title', 'label' => 'Step Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                ['key' => 'text', 'label' => 'Step Text', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:500']],
                ['key' => 'image_media_id', 'label' => 'Step Photo (media ID)', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id']],
            ]],
            $this->mediaField('bg_media_id', 'Background Pattern (damask)', device: false),
        ];
    }
}
