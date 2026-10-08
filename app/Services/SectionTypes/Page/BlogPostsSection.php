<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * "Our Blog Post" (design 08 Blog.png): the two newest live posts as large
 * cards (photo pair, meta pills, title, excerpt) plus the next three as
 * mini rows. Fed by the Blog module — content is managed in Admin → Blog.
 */
class BlogPostsSection extends PageSectionType
{
    public function key(): string
    {
        return 'blog-posts';
    }

    public function label(): string
    {
        return 'Blog Posts (Latest, Cards + Mini Rows)';
    }

    public function icon(): string
    {
        return 'fas fa-newspaper';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'limit', 'label' => 'Maximum posts (2 large + rest as mini rows)', 'type' => 'number', 'default' => 5, 'rules' => ['nullable', 'integer', 'between:1,8']],
            ['key' => 'button_label', 'label' => 'Button Label', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:60']],
            ['key' => 'button_url', 'label' => 'Button URL', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            $this->buttonStyleField(),
            $this->mediaField('bg_media_id', 'Background Texture', device: false),
        ];
    }
}
