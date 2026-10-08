<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class HeroSection extends PageSectionType
{
    public function key(): string
    {
        return 'hero';
    }

    public function label(): string
    {
        return 'Hero Banner';
    }

    public function icon(): string
    {
        return 'fas fa-star';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(withEyebrow: true),
            $this->richTextField('text', 'Intro Text'),
            $this->buttonsRepeater(2),
            $this->mediaField('bg_media_id', 'Background Image (also used as video poster/fallback)'),
            ['key' => 'bg_video_media_id', 'label' => 'Background Video (MP4 from Media Library)', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id'], 'hint' => 'Autoplays muted, loops, no controls. The Background Image shows while the video loads and on browsers that block playback.'],
            ['key' => 'overlay_opacity', 'label' => 'Dark Overlay (0–90%)', 'type' => 'number', 'default' => 50, 'rules' => ['nullable', 'integer', 'between:0,90']],
            ['key' => 'overlay_gradient', 'label' => 'Overlay Gradient (Gradient Library slug — overrides the flat overlay)', 'type' => 'text', 'rules' => ['nullable', 'string', 'exists:gradients,slug']],
            ['key' => 'height', 'label' => 'Height', 'type' => 'select', 'device' => true, 'default' => 'full', 'options' => ['banner' => 'Compact Banner (200px)', 'small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'half' => 'Half screen (50vh)', 'full' => 'Full screen (100vh)'], 'rules' => ['nullable', 'in:banner,small,medium,large,half,full']],
            $this->alignmentField(),
            ['key' => 'content_width', 'label' => 'Content Max Width (px)', 'type' => 'number', 'default' => 780, 'rules' => ['nullable', 'integer', 'between:320,1600']],
            ['key' => 'entrance', 'label' => 'Content Entrance Animation', 'type' => 'select', 'default' => 'fade-up', 'options' => ['none' => 'None', 'fade-up' => 'Fade up', 'fade-in' => 'Fade in', 'zoom-in' => 'Zoom in'], 'rules' => ['nullable', 'in:none,fade-up,fade-in,zoom-in']],
            ['key' => 'scroll_indicator', 'label' => 'Scroll Indicator (right edge line)', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'show_breadcrumb', 'label' => 'Show Breadcrumb (Home / …)', 'type' => 'toggle', 'default' => false, 'rules' => ['nullable', 'boolean']],
            ['key' => 'breadcrumb_label', 'label' => 'Breadcrumb Current-Page Label (defaults to the Heading)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:150']],
            // Optional middle breadcrumb trail item (e.g. Home / The Journal /
            // Post Title). Blank on every page except where explicitly set —
            // purely additive, does not change the default Home / Current
            // 2-level breadcrumb used everywhere else.
            ['key' => 'breadcrumb_parent_label', 'label' => 'Breadcrumb Middle Item Label (optional — e.g. "The Journal")', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:150']],
            ['key' => 'breadcrumb_parent_url', 'label' => 'Breadcrumb Middle Item URL (optional)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
        ];
    }
}
