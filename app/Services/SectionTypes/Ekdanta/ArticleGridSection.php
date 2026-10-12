<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Blog: all articles. Topic chips (blog categories) and the hero's search
 * box filter the grid; cards rise as they come into view (design: Blog
 * b-posts). Works without JS too: the chips and search are links/forms
 * this section reads (?topic=, ?search=).
 */
class ArticleGridSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'article-grid';
    }

    protected function anchor(): string
    {
        return 'b-posts';
    }

    public function label(): string
    {
        return 'Ekdanta · Article Grid';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'ALL ARTICLES', 40),
            $this->text('heading', 'Heading', 'Fresh from the team.', 80),
            $this->text('results_heading', 'Heading while searching ({q} = the words)', 'Results for “{q}”', 80),
            $this->text('filter_label', 'Topic chips: name read by screen readers', 'Filter by topic', 60, false),
            $this->text('all_label', 'First chip', 'All', 30),
            $this->text('empty_title', 'Nothing found: title', 'No articles match that.', 80),
            $this->text('empty_text', 'Nothing found: text', 'Try another word, or pick a different topic.', 120),
            $this->text('read_time', 'Reading time ({minutes})', '{minutes} min read', 30, false),
            $this->toggle('skip_featured', 'Leave out the featured article (shown above)', true),
        ];
    }
}
