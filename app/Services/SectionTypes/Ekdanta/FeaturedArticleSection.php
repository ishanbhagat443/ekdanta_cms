<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Blog: the featured article (the newest post marked "featured", else the
 * newest post). The card opens like a curtain, the cover settles and the
 * text follows (design: Blog b-feat).
 */
class FeaturedArticleSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'featured-article';
    }

    protected function anchor(): string
    {
        return 'b-feat';
    }

    public function label(): string
    {
        return 'Ekdanta · Featured Article';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'FEATURED ARTICLE', 40),
            $this->text('heading', 'Heading', 'Start with this one.', 80),
            $this->text('meta', 'Byline ({author}, {minutes})', 'By {author} · {minutes} min read', 80),
            $this->text('button_label', 'Button', 'Read the article', 40),
            $this->image('mark', 'Byline picture', 'ekdanta/e-mark'),
        ];
    }
}
