<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Blog: the hero. The notebook masthead and headline, a search box that
 * filters the article grid below, a shuffling stack of the latest posts
 * and a ticker of topics (design: Blog b-top).
 */
class BlogHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'blog-hero';
    }

    protected function anchor(): string
    {
        return 'b-top';
    }

    public function label(): string
    {
        return 'Ekdanta · Blog Hero (Notebook)';
    }

    protected function content(): array
    {
        return [
            $this->text('masthead', 'Masthead', 'THE EKDANTA NOTEBOOK', 60),
            $this->text('masthead_right', 'Masthead, right', 'Surat · Ahmedabad', 60),
            $this->text('heading', 'Heading, line 1', 'Seekho.', 40),
            $this->text('heading_2', 'Heading, line 2 (green)', 'Phir badho.', 40),
            $this->lines('text', 'Text', 'Practical notes on branding, social media, ads, websites and sales, from the team that does this work every day.', 300),
            $this->text('search_label', 'Search box: name read by screen readers', 'Search articles', 60, false),
            $this->text('placeholder', 'Search box: hint', 'Search articles, e.g. reels', 60),
            $this->text('search_button', 'Search button', 'Search', 20),
            $this->text('byline', 'Stack cards: byline ({author})', 'By {author}', 60, false),
            $this->lines('topics', 'Ticker topics (one per line)', "Social media\nMeta ads\nBranding\nPackaging\nWebsites\nProduct shoots\nReels\nCRM\nSales\nFestival marketing\nLead generation\nSEO", 600, false),
        ];
    }
}
