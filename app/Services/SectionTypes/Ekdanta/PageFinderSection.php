<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

use App\Models\CollectionItem;
use App\Models\Page;
use App\Models\Post;
use App\Support\PageRoles;

/**
 * 404 page: back on track. Six link cards deal in; a search box finds pages,
 * services and articles as you type (design: Not-Found n-routes).
 */
class PageFinderSection extends EkdantaSection
{
    public const ICONS = ['home' => 'House', 'services' => 'Squares', 'work' => 'Photos', 'about' => 'People', 'blog' => 'Pen', 'contact' => 'Phone'];

    protected function slug(): string
    {
        return 'page-finder';
    }

    protected function anchor(): string
    {
        return 'n-routes';
    }

    public function label(): string
    {
        return 'Ekdanta · Page Finder';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', "LET'S GET YOU BACK ON TRACK", 60),
            $this->lines('heading', 'Heading (a new line breaks it on desktop)', "Shayad aap\nyeh dhoondh rahe the.", 120),
            $this->text('search_label', 'Search box: name read by screen readers', 'Search the Ekdanta website', 80, false),
            $this->text('placeholder', 'Search box: hint', 'Search pages, services or articles', 60),
            $this->text('clear_label', 'Clear button: name read by screen readers', 'Clear search', 40, false),
            $this->text('count_one', 'Results: one match ({n} = number)', '{n} match', 40, false),
            $this->text('count_many', 'Results: several matches', '{n} matches', 40, false),
            $this->text('count_none', 'Results: no matches', 'No matches', 40, false),
            $this->text('no_hits', 'Results: hint when nothing matches', 'Try “reels”, “website” or “ads”.', 120, false),
            $this->text('kind_page', 'Results: tag for pages', 'PAGE', 20, false),
            $this->text('kind_service', 'Results: tag for services', 'SERVICE', 20, false),
            $this->text('kind_article', 'Results: tag for articles', 'ARTICLE', 20, false),
            $this->rows('cards', 'Cards (up to six)', [
                $this->select('icon', 'Icon', self::ICONS),
                $this->text('title', 'Title', null, 40),
                $this->text('text', 'Text', null, 120),
                $this->text('text_short', 'Text on phones', null, 80),
                $this->link('link', 'Link goes to'),
                $this->select('look', 'Colour', ['paper' => 'Light', 'green' => 'Green']),
            ], [
                ['icon' => 'home', 'title' => 'Home', 'text' => 'Start again from the homepage.', 'text_short' => 'Start again from the homepage.', 'link' => '/', 'look' => 'paper'],
                ['icon' => 'services', 'title' => 'Services', 'text' => 'Branding, social media, ads, websites, shoots and CRM.', 'text_short' => 'Branding, social, ads, websites and more.', 'link' => '/services', 'look' => 'paper'],
                ['icon' => 'work', 'title' => 'Work', 'text' => "Case studies from brands we've helped grow.", 'text_short' => "Brands we've helped grow.", 'link' => '/work', 'look' => 'paper'],
                ['icon' => 'about', 'title' => 'About us', 'text' => 'Meet the team behind Ekdanta.', 'text_short' => 'The team behind Ekdanta.', 'link' => '/about', 'look' => 'paper'],
                ['icon' => 'blog', 'title' => 'Blog', 'text' => 'Practical tips on reels, ads and branding.', 'text_short' => 'Tips on reels, ads and branding.', 'link' => '/blog', 'look' => 'paper'],
                ['icon' => 'contact', 'title' => 'Contact us', 'text' => 'Call, WhatsApp or visit us in Surat.', 'text_short' => 'Call, WhatsApp or visit us.', 'link' => '/contact', 'look' => 'green'],
            ], 6),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => ['placeholder' => 'Search pages, services, articles']];
    }

    /**
     * What the search box looks through: published pages (not the 404,
     * thank-you or template pages), services with their own page and live
     * posts. Each entry: kind, title, address, extra words to match.
     *
     * @return list<array{k: string, t: string, u: string, w: string}>
     */
    public static function index(): array
    {
        $hidden = PageRoles::hiddenFromSitemap();
        $pages = Page::query()->where('status', Page::STATUS_PUBLISHED)->with('seoMeta')->orderBy('id')->get()
            ->reject(fn (Page $p) => in_array($p->id, $hidden, true) || $p->setting('template_for'))
            ->map(fn (Page $p) => ['k' => 'page', 't' => $p->title, 'u' => url($p->isHomePage() ? '/' : '/'.$p->slug),
                'w' => trim($p->seoMeta?->meta_keywords.' '.$p->seoMeta?->meta_description)]);

        $services = CollectionItem::query()->where('collection', 'services')->published()->orderBy('sort_order')->get()
            ->filter(fn (CollectionItem $s) => $s->url() !== null)
            ->map(fn (CollectionItem $s) => ['k' => 'service', 't' => $s->title, 'u' => $s->url(),
                'w' => trim($s->get('short_name').' '.$s->get('one_liner').' '.$s->get('group'))]);

        $posts = Post::query()->live()->with('categories')->latest('published_at')->limit(50)->get()
            ->map(fn (Post $p) => ['k' => 'article', 't' => $p->title, 'u' => route('front.blog.show', $p->slug),
                'w' => trim($p->categories->pluck('name')->implode(' ').' '.$p->excerpt)]);

        return [...$pages->values(), ...$services->values(), ...$posts->values()];
    }
}
