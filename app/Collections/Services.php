<?php

declare(strict_types=1);

namespace App\Collections;

use App\Models\CollectionItem;

/**
 * Every service. Most show in their group's grid; six also have their own
 * page at /services/{slug} (Has a page).
 */
class Services extends CollectionType
{
    /** Service Deep Dive tile artworks (resources/views/frontend/sections/ekdanta/tiles). */
    public const TILES = [
        'brand-strategy' => 'Target and arrow', 'brand-identity' => 'Mark drawn in a circle', 'visual-identity' => 'Colour swatches', 'brand-guidelines' => 'Type specimen',
        'packaging-design' => 'Box and lid', 'brand-positioning' => 'Position map', 'social-media-management' => 'Post grid', 'performance-marketing' => 'Growth chart',
        'meta-ads' => 'Ad and tap', 'lead-generation' => 'Form filling in', 'seo' => 'Search ranking', 'website-development' => 'Page building',
        'lead-funnel-strategy' => 'Paths to an enquiry', 'sales-funnel-design' => 'Funnel', 'lead-nurturing' => 'Follow-up messages', 'sales-consulting' => 'Call checklist',
        'crm-and-app-development' => 'Pipeline board', 'conversion-optimisation' => 'Before and after bars', 'brand-and-corporate-films' => 'Film frame and timecode',
        'product-photography' => 'Camera flash', 'product-videos' => 'Video player', 'motion-graphics' => 'Shapes in motion', 'ugc-content' => 'Creator post',
        'influencer-and-model-shoots' => 'Camera and burst',
    ];

    public const SCENES = ['social' => 'Social media feed', 'ads' => 'Ads dashboard', 'branding' => 'Brand board', 'website' => 'Website build', 'shoots' => 'Camera and reels', 'crm' => 'CRM pipeline'];

    public function key(): string { return 'services'; }
    public function label(): string { return 'Services'; }
    public function singular(): string { return 'Service'; }
    public function icon(): string { return 'fas fa-concierge-bell'; }
    public function template(): ?string { return 'service'; }
    public function pagePrefix(CollectionItem $item): ?string { return $item->get('has_page') ? '/services' : null; }
    public function hint(): string { return 'Grid services appear under their group on Home and Services. Services with a page open at /services/its-slug through the page marked "Template for: Service".'; }

    public function fields(): array
    {
        return [
            $this->text('group', 'Group (slug from Service groups)', null, 120),
            $this->text('short_name', 'Short name', null, 80),
            $this->text('lower_name', 'Name inside a sentence (e.g. "social media")', null, 80),
            $this->text('one_liner', 'One-liner', null, 200),
            $this->select('tile', 'Services page: tile artwork', self::TILES),
            $this->toggle('in_grid', 'Show in the group grid', true),
            $this->toggle('has_page', 'Has its own page'),
            $this->text('group_label', 'Page: group label (e.g. DIGITAL · PERFORMANCE)', null, 80),
            $this->select('scene', 'Page: hero scene', self::SCENES),
            $this->colour('hero_bg', 'Page: hero background', null, true),
            $this->colour('hero_fg', 'Page: hero text', null, true),
            $this->colour('hero_glow', 'Page: hero glow', null, true),
            $this->colour('hero_line', 'Page: hero lines', null, true),
            $this->colour('hero_accent', 'Page: accent', null, true),
            $this->colour('hero_accent_fg', 'Page: text on accent', null, true),
            $this->lines('promise', 'Page: promise', null, 400),
            $this->lines('good_for', 'Page: good for (one per line)', null, 300),
            $this->rows('included', "Page: what's included", [$this->text('title', 'Title'), $this->text('text', 'Text')], [], 12),
            $this->rows('steps', 'Page: how it works', [$this->text('title', 'Title'), $this->text('text', 'Text')], [], 8),
            $this->text('proof_title', 'Page: related work heading', null, 120),
            $this->text('proof_cases', 'Page: related case studies (slugs, comma separated)', null, 300),
            $this->rows('proof_placeholders', 'Page: related work cards (when there are no case studies)', [$this->text('title', 'Title'), $this->text('text', 'Text')], [], 3),
            $this->lines('price_factors', 'Page: what the price depends on (one per line)', null, 800),
            $this->text('related', 'Page: works well with (service slugs, comma separated)', null, 300),
        ];
    }
}
