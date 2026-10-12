<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Blog article: the text, with a reading rail beside it (contents that
 * follow the heading you are on, a ring that counts down the minutes left,
 * share), then the tags and who wrote it (design: Blog-Post p-body).
 * The text is drawn once for every screen size (a phone stacks the rail
 * above it), so its headings keep one id each for the contents links.
 */
class ArticleBodySection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'article-body';
    }

    protected function anchor(): string
    {
        return 'p-body';
    }

    public function label(): string
    {
        return 'Ekdanta · Article Body + Reading Rail';
    }

    protected function content(): array
    {
        return [
            $this->text('rail_label', 'Rail (screen readers)', 'Article tools', 60, false),
            $this->text('toc_label', 'Contents label', 'ON THIS PAGE', 40, false),
            $this->text('left_label', 'Minutes left ({minutes})', '{minutes} min left', 40, false),
            $this->text('done_label', 'At the end', 'Done reading', 40, false),
            $this->text('read_label', 'Read so far ({percent})', '{percent}% read', 40, false),
            $this->text('share_label', 'Share label', 'SHARE THIS ARTICLE', 40, false),
            $this->text('share_whatsapp', 'Share on WhatsApp (screen readers)', 'Share on WhatsApp', 60, false),
            $this->text('share_linkedin', 'Share on LinkedIn (screen readers)', 'Share on LinkedIn', 60, false),
            $this->text('copy_label', 'Copy link (screen readers)', 'Copy link', 60, false),
            $this->text('copied_label', 'After copying', 'Link copied', 60, false),
            $this->text('tags_label', 'Tags label', 'TAGS', 30, false),
            $this->text('author_kicker', 'Author: small line', 'WRITTEN BY', 40, false),
            $this->text('author_name', "Author: name (empty = the article's author)", '', 80, false),
            $this->lines('author_bio', 'Author: about', 'We make reels, posts, ads and websites for manufacturers and brands across Gujarat, from Surat and Ahmedabad.', 400, false),
            $this->text('author_link_label', 'Author: link', 'Meet the team', 40, false),
            $this->link('author_link', 'Author: link goes to', '/about'),
            $this->image('author_image', 'Author: picture', 'ekdanta/e-mark'),
        ];
    }
}
