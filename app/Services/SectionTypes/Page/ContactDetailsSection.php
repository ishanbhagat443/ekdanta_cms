<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Page;

/**
 * Contact information cards (Designs/Contact Us Page 03 Company Details):
 * a responsive 3 / 2 / 1 grid of cards, each with a gradient shield icon,
 * a title with a gold underline, and a description, framed by garland
 * ornaments on the left and right. Every value is CMS-editable; the card
 * icon shield reuses the theme CTA gradient (no bespoke button CSS).
 * Section-level visibility / sort / background / custom class / ID /
 * Dev-Mode editor come from the shared Page Builder wrapper.
 */
class ContactDetailsSection extends PageSectionType
{
    public function key(): string
    {
        return 'contact-details';
    }

    public function label(): string
    {
        return 'Contact Details (cards)';
    }

    public function icon(): string
    {
        return 'fas fa-address-card';
    }

    public function fields(): array
    {
        return [
            ...$this->headingFields(),
            ['key' => 'garland_left_media_id', 'label' => 'Garland — Left ornament', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id']],
            ['key' => 'garland_right_media_id', 'label' => 'Garland — Right ornament', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id']],
            ['key' => 'title_underline_media_id', 'label' => 'Card title underline image (gold line)', 'type' => 'media', 'rules' => ['nullable', 'integer', 'exists:media,id']],
            ['key' => 'card_bg', 'label' => 'Card Background Color', 'type' => 'color', 'default' => '#ffffff', 'rules' => ['nullable', 'string', 'max:30']],
            ['key' => 'card_radius', 'label' => 'Card Border Radius (px)', 'type' => 'number', 'default' => 6, 'rules' => ['nullable', 'integer', 'between:0,60']],
            ['key' => 'card_gap', 'label' => 'Card Spacing / Gap (px)', 'type' => 'number', 'default' => 24, 'rules' => ['nullable', 'integer', 'between:0,80']],
            ['key' => 'card_shadow', 'label' => 'Card Shadow', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ['key' => 'cards', 'label' => 'Cards', 'type' => 'repeater', 'rules' => ['max:6'], 'fields' => [
                ['key' => 'icon', 'label' => 'Icon class (fas fa-phone / fa-envelope / fa-map-marker-alt)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60']],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                ['key' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:600']],
                ['key' => 'link', 'label' => 'Link (tel: / mailto: / URL — optional)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:300']],
                ['key' => 'is_visible', 'label' => 'Visible', 'type' => 'toggle', 'default' => true, 'rules' => ['nullable', 'boolean']],
            ]],
        ];
    }
}
