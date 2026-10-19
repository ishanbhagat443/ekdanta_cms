<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

use App\Support\LegalContent;

/**
 * Privacy Policy and Terms of Service: "In short" (design: Privacy l-short,
 * Terms t-short). Four cards are dealt from a stack into place as you
 * scroll; two by two on phones.
 */
class LegalShortSection extends EkdantaSection
{
    /** The cards' icons, as drawn (24 × 24, stroked). */
    public const ICONS = [
        'form' => '<rect x="5" y="3" width="14" height="18" rx="2"></rect><path d="M9 8h6M9 12h6M9 16h3"></path>',
        'reply' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path><path d="M9 12h6"></path>',
        'key' => '<circle cx="8" cy="15" r="4"></circle><path d="M11 12l8-8M16 7l2 2M14 9l2 2"></path>',
        'cookie' => '<path d="M12 3a9 9 0 1 0 9 9 4 4 0 0 1-4-4 4 4 0 0 1-5-5z"></path><path d="M8.5 11.5h.01M12 15.5h.01M15.5 13h.01"></path>',
        'chat' => '<path d="M4 5h16v11H9l-5 4z"></path><path d="M8 9h8M8 12h5"></path>',
        'sign' => '<path d="M4 20c3-1 5-6 7-6s1 4 3 4 3-3 6-3"></path><path d="M14 4l6 6-8 8H6v-6z"></path>',
        'chart' => '<path d="M4 20V4M4 20h16"></path><path d="M8 15l3-4 3 2 5-6" stroke-dasharray="2 2"></path>',
        'scale' => '<path d="M12 4v16M6 20h12M5 8h14"></path><path d="M5 8l-3 6a3 3 0 0 0 6 0zM19 8l-3 6a3 3 0 0 0 6 0z"></path>',
    ];

    protected function slug(): string
    {
        return 'legal-short';
    }

    protected function anchor(): string
    {
        return 'l-short';
    }

    public function label(): string
    {
        return 'Ekdanta · Legal In Short';
    }

    protected function content(): array
    {
        $d = LegalContent::sections('privacy-policy')['ekdanta-legal-short'];
        $icons = array_combine($k = array_keys(self::ICONS), array_map('ucfirst', $k));

        return [
            $this->text('eyebrow', 'Small line above the title', $d['eyebrow'], 60),
            $this->text('title', 'Title', $d['title'], 60),
            $this->lines('text', 'Text', $d['text'], 300),
            $this->rows('cards', 'Cards (four fit)', [
                $this->text('title', 'Title', null, 60),
                $this->lines('text', 'Text', null, 200),
                $this->select('icon', 'Icon', $icons),
            ], $d['cards'], 4),
            $this->colour('bg', 'Background', $d['bg']),
            $this->colour('fg', 'Text', $d['fg']),
            $this->colour('accent', 'Small line', $d['accent']),
            $this->colour('muted', 'Text under the title and on the cards', $d['muted']),
            $this->colour('card_bg', 'Cards', $d['card_bg']),
        ];
    }
}
