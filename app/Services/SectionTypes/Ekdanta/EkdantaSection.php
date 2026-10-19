<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

use App\Services\SectionTypes\Page\PageSectionType;

/**
 * Base for the Ekdanta section pack ("Ekdanta · …" in Add a section).
 *
 * A type is its admin name, its design id (also the default anchor) and its
 * fields; the view is resources/views/frontend/sections/ekdanta/<slug>, the
 * design motion resources/motion/ekdanta/<slug>.json. Every default is the
 * design's exact text, so a new section looks like the design at once.
 */
abstract class EkdantaSection extends PageSectionType
{
    /** The part of the key after "ekdanta-"; also the view and motion file name. */
    abstract protected function slug(): string;

    /** The design's section id, the default anchor ("j-obstacles"). */
    abstract protected function anchor(): string;

    /** @return list<array<string, mixed>> */
    abstract protected function content(): array;

    /**
     * Sections of the nine pages with a tablet design (About, Work, Blog,
     * Case study, Service detail, Blog article, 404, Privacy, Terms) and the
     * Big Brand Finale: on an upright tablet the phone layout widens to a
     * 724 px column. Home, Services, Contact and Thank you keep the phone
     * layout, larger and centred.
     */
    private const TABLET_WIDE = [
        'about-hero', 'our-story', 'four-promises', 'team-roll-call', 'who-we-work-with',
        'work-hero', 'case-studies-scroller', 'reels-arc', 'client-logo-wall',
        'case-hero', 'case-overview', 'case-gallery', 'case-results', 'next-case',
        'service-hero', 'whats-included', 'how-it-works', 'related-work', 'service-pricing-faq', 'works-well-with',
        'blog-hero', 'featured-article', 'article-grid', 'whatsapp-tips', 'article-hero', 'article-body', 'keep-reading',
        'not-found-hero', 'page-finder', 'report-link',
        'legal-hero', 'legal-short', 'legal-body', 'legal-questions', 'finale',
    ];

    public function key(): string
    {
        return 'ekdanta-'.$this->slug();
    }

    public function tabletWide(): bool
    {
        return in_array($this->slug(), self::TABLET_WIDE, true);
    }

    public function group(): string
    {
        return 'Ekdanta';
    }

    public function icon(): string
    {
        return 'fas fa-shapes';
    }

    public function view(): string
    {
        return 'frontend.sections.ekdanta.'.$this->slug();
    }

    public function fields(): array
    {
        return [
            ...$this->content(),
            ['key' => 'anchor', 'label' => 'Anchor: links to this section use #'.$this->anchor(), 'type' => 'text', 'default' => $this->anchor(),
                'rules' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z][A-Za-z0-9_-]*$/']],
        ];
    }

    public function motionDefaults(): array
    {
        $file = resource_path('motion/ekdanta/'.$this->slug().'.json');

        return is_file($file) ? (array) json_decode((string) file_get_contents($file), true) : [];
    }

    /** Every part its design motion moves, for the Motion tab's part list. */
    public function motionParts(): array
    {
        $parts = [];
        foreach ($this->motionDefaults() as $device) {
            $parts[] = $device['steps']['part'] ?? null;
            foreach ((array) ($device['tracks'] ?? []) as $track) {
                $parts[] = $track['part'] ?? null;
            }
        }

        return array_values(array_unique(array_filter($parts, fn ($p) => $p !== null && $p !== '@self')));
    }
}
