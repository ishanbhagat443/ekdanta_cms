<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

class VideoSection extends PageSectionType
{
    public function key(): string
    {
        return 'video';
    }

    public function label(): string
    {
        return 'Video';
    }

    public function icon(): string
    {
        return 'fas fa-play-circle';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            $this->mediaField('poster_media_id', 'Poster Image'),
            ['key' => 'video_url', 'label' => 'Video URL (YouTube/Vimeo/MP4)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            ['key' => 'autoplay', 'label' => 'Autoplay in lightbox', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
        ];
    }
}
