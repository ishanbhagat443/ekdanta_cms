<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * "The Threads of Our Heritage" (About page design 07 Our History.png):
 * a horizontally scrollable zigzag timeline of scalloped year badges
 * (alternating gold/orange) connected by a dashed curve, each linked to a
 * photo card that alternates above/below the badge row. Built as an
 * unbounded horizontal scroll-snap track (not a fixed grid) because more
 * years are added over time via the CMS — the design itself shows the
 * last visible card cut off at the frame edge, confirming it continues.
 */
class HistoryTimelineSection extends PageSectionType
{
    public function key(): string
    {
        return 'history-timeline';
    }

    public function label(): string
    {
        return 'History Timeline (Scrollable, Unbounded Years)';
    }

    public function icon(): string
    {
        return 'fas fa-scroll';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'milestones', 'label' => 'Milestones (add as many years as needed)', 'type' => 'repeater', 'rules' => ['max:50'], 'fields' => [
                ['key' => 'year', 'label' => 'Year', 'type' => 'text', 'rules' => ['required', 'string', 'max:20']],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:300']],
                ['key' => 'image_media_id', 'label' => 'Photo', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id']],
            ]],
            $this->mediaField('bg_media_id', 'Background Texture', device: false),
        ];
    }
}
