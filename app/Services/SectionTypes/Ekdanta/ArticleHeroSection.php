<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Blog article: the title types itself out, with an orange swoosh under
 * its green word, then the cover opens: the article's cover image, or the
 * brochure-versus-reel drawing (design: Blog-Post p-top).
 */
class ArticleHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'article-hero';
    }

    protected function anchor(): string
    {
        return 'p-top';
    }

    public function label(): string
    {
        return 'Ekdanta · Article Hero (Typewriter)';
    }

    protected function content(): array
    {
        return [
            $this->text('back_label', 'Back link', 'All articles', 40),
            $this->link('back_link', 'Back link goes to', '/blog'),
            $this->lines('highlight', 'Green words (one per line: the first one found in the title turns green, with the orange swoosh)', 'reels', 500, false),
            $this->text('byline', 'Byline ({author})', 'By {author}', 80),
            $this->text('meta', 'Date line ({date}, {minutes})', 'Published {date} · {minutes} min read', 80),
            $this->image('avatar', 'Byline picture', 'ekdanta/e-mark'),
            $this->text('share_label', 'Share label', 'SHARE', 30),
            $this->text('share_whatsapp', 'Share on WhatsApp (screen readers)', 'Share on WhatsApp', 60, false),
            $this->text('share_linkedin', 'Share on LinkedIn (screen readers)', 'Share on LinkedIn', 60, false),
            $this->text('copy_label', 'Copy link (screen readers)', 'Copy link', 60, false),
            $this->text('copied_label', 'After copying', 'Link copied', 60, false),
            $this->lines('drawing_for', 'Articles that show the drawing instead of their cover image (address after /blog/, one per line). Articles without a cover image always do.', 'why-your-factory-needs-reels-not-just-brochures', 2000, false),
            $this->text('drawing_outline', 'Drawing: outlined words', "Show, don't tell.", 60, false),
            $this->text('brochure_alt', 'Drawing: brochure (screen readers)', 'A printed company brochure listing generic claims', 200, false),
            $this->text('brochure_kicker', 'Drawing: brochure kicker', 'COMPANY PROFILE', 40, false),
            $this->lines('brochure_title', 'Drawing: brochure title', "Quality you\ncan trust.", 80, false),
            $this->lines('brochure_points', 'Drawing: brochure points (one per line)', "Modern plant\nQuality products\nTimely delivery\nCustomer satisfaction", 300, false),
            $this->text('brochure_page', 'Drawing: brochure page number', 'Page 1 of 24', 30, false),
            $this->text('tells', 'Drawing: brochure sticker', 'TELLS', 20, false),
            $this->text('shows', 'Drawing: phone sticker', 'SHOWS', 20, false),
            $this->text('phone_alt', 'Drawing: phone (screen readers)', 'A phone playing a short factory reel', 200, false),
            $this->text('handle', 'Drawing: reel account', 'yourfactory', 30, false),
            $this->text('follow', 'Drawing: follow button', 'Follow', 20, false),
            $this->text('likes', 'Drawing: likes', '1.2k', 10, false),
            $this->text('comments', 'Drawing: comments', '86', 10, false),
            $this->text('caption', 'Drawing: reel caption', 'POV: every part is checked before it ships.', 120, false),
            $this->text('audio', 'Drawing: reel audio', 'Original audio', 40, false),
            $this->text('bubble_label', 'Drawing: message label', 'New enquiry', 30, false),
            $this->text('bubble_time', 'Drawing: message time', 'now', 20, false),
            $this->rows('bubbles', 'Drawing: messages (three fit)', [
                $this->text('initial', 'Initial', null, 2),
                $this->text('text', 'Message', null, 120),
            ], [
                ['initial' => 'R', 'text' => 'Saw your reel. Can you share the price list?'],
                ['initial' => 'M', 'text' => 'Do you supply in Pune? We need 500 pieces.'],
                ['initial' => 'K', 'text' => 'Can we visit your plant next week?'],
            ], 3),
        ];
    }
}
