<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Footer;

use App\Services\SectionTypes\SectionType;

/**
 * "Follow Us On Instagram" strip (design 09 Instagram.png): blackletter
 * heading + handle and a full-width six-photo strip. Lives in the Footer
 * Builder so it appears above the footer on every page.
 */
class InstagramFeedSection extends SectionType
{
    public function key(): string
    {
        return 'instagram-feed';
    }

    public function label(): string
    {
        return 'Instagram Strip (Photos + Handle)';
    }

    public function icon(): string
    {
        return 'fab fa-instagram';
    }

    public function allowedAreas(): array
    {
        return ['footer', 'page'];
    }

    public function fields(): array
    {
        return [
            ['key' => 'heading', 'label' => 'Heading', 'type' => 'text', 'device' => true, 'rules' => ['nullable', 'string', 'max:150']],
            ['key' => 'handle', 'label' => 'Handle (e.g. @Globalsilkmillspvt_ltd)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
            ['key' => 'profile_url', 'label' => 'Instagram Profile URL', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            ['key' => 'images', 'label' => 'Photos (design shows 6)', 'type' => 'repeater', 'rules' => ['max:8'], 'fields' => [
                ['key' => 'image_media_id', 'label' => 'Photo', 'type' => 'media', 'rules' => ['required', 'integer', 'exists:media,id']],
                ['key' => 'url', 'label' => 'Link (optional, defaults to profile)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500']],
            ]],
            ['key' => 'bg_media_id', 'label' => 'Background Texture', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id']],
        ];
    }
}
