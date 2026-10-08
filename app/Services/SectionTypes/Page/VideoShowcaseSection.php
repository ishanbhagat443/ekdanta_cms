<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * Repeater of video blocks with alternating photo/text position (About
 * page design 05 Founder Video.png): each item is its own heading + body
 * + poster photo + play button opening a lightbox video, mirroring the
 * Home page's zigzag Working Process convention. Unlike the single-video
 * "video" SectionType, this supports any number of stacked video blocks.
 */
class VideoShowcaseSection extends PageSectionType
{
    public function key(): string
    {
        return 'video-showcase';
    }

    public function label(): string
    {
        return 'Video Showcase (Alternating Blocks)';
    }

    public function icon(): string
    {
        return 'fas fa-film';
    }

    public function fields(): array
    {
        return [
            ['key' => 'items', 'label' => 'Video Blocks', 'type' => 'repeater', 'rules' => ['max:6'], 'fields' => [
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:200']],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
                ['key' => 'poster_media_id', 'label' => 'Poster Photo', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id']],
                ['key' => 'video_url', 'label' => 'Video URL (YouTube/Vimeo/MP4)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            ]],
            ['key' => 'autoplay', 'label' => 'Autoplay in lightbox', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
