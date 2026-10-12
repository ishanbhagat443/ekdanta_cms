<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Blog article: more articles, its related ones first, then its topic's
 * newest, then the newest (design: Blog-Post p-more).
 */
class KeepReadingSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'keep-reading';
    }

    protected function anchor(): string
    {
        return 'p-more';
    }

    public function label(): string
    {
        return 'Ekdanta · Keep Reading';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'KEEP READING', 40),
            $this->text('heading', 'Heading', 'More from the notebook.', 80),
            $this->text('button_label', 'Button', 'All articles', 40),
            $this->link('button_link', 'Button link', '/blog'),
            $this->text('read_time', 'Card: reading time ({minutes})', '{minutes} min read', 40),
            $this->number('count', 'How many articles', 3, 1, 6),
        ];
    }
}
