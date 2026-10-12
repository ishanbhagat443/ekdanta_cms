<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Thank-you page: while you wait. Three link cards deal in (our work, our
 * Instagram, the blog); the work photos fan out, the phone feed scrolls and
 * the notebook rolls through post titles (design: Thank-You t-wait).
 */
class WhileYouWaitSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'while-you-wait';
    }

    protected function anchor(): string
    {
        return 't-wait';
    }

    public function label(): string
    {
        return 'Ekdanta · While You Wait';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'WHILE YOU WAIT', 40),
            $this->text('heading', 'Heading', 'Tab tak, thoda dekh lo.', 80),
            $this->lines('text', 'Text (desktop only)', 'Get to know us a little better before we talk.', 200, false),

            $this->text('work_label', 'Card 1: small line', 'SEE OUR WORK', 40),
            $this->text('work_title', 'Card 1: title', "What we've made for brands like yours.", 80),
            $this->lines('work_text', 'Card 1: text (desktop only)', 'From precast and CNC machines to solar, skincare and Himalayan treks.', 200, false),
            $this->text('work_link_label', 'Card 1: link text', 'View our work', 40),
            $this->link('work_link', 'Card 1: link goes to', '/work'),
            $this->image('work_photo_1', 'Card 1: photo, left', 'ekdanta/case-trilok-infratech'),
            $this->image('work_photo_2', 'Card 1: photo, right', 'ekdanta/case-heaven-solar'),
            $this->image('work_photo_3', 'Card 1: photo, front', 'ekdanta/case-yantra-design'),

            $this->text('insta_label', 'Card 2: small line', 'FOLLOW ALONG', 40),
            $this->text('insta_title', 'Card 2: title', '@ekdanta.in on Instagram.', 80),
            $this->lines('insta_text', 'Card 2: text', 'Fresh reels and posts from our latest shoots and launches.', 200),
            $this->text('insta_link_label', 'Card 2: link text', 'Open Instagram', 40),
            $this->link('insta_link', 'Card 2: link goes to', 'https://www.instagram.com/ekdanta.in/'),
            $this->text('feed_name', 'Card 2: name on the phone', 'ekdanta.in', 40, false),
            $this->image('feed_mark', 'Card 2: logo on the phone', 'ekdanta/e-mark'),
            $this->image('feed_1', 'Card 2: feed photo 1', 'ekdanta/case-valleyrides'),
            $this->image('feed_2', 'Card 2: feed photo 2', 'ekdanta/case-srk-dairy'),
            $this->image('feed_3', 'Card 2: feed photo 3', 'ekdanta/case-her-and-he'),

            $this->text('blog_label', 'Card 3: small line', 'READ THE BLOG', 40),
            $this->text('blog_title', 'Card 3: title', 'Practical tips, without the jargon.', 80),
            $this->lines('blog_text', 'Card 3: text', 'Reels, ads, branding and websites, explained simply.', 200),
            $this->text('blog_link_label', 'Card 3: link text', 'Read the blog', 40),
            $this->link('blog_link', 'Card 3: link goes to', '/blog'),
            $this->text('notebook_label', 'Card 3: notebook tag', 'THE EKDANTA NOTEBOOK', 40),
            $this->lines('notebook_titles', 'Card 3: notebook titles, shown in turn (three, one per line)', "Why your factory needs reels, not just brochures\nFestival posts that bring enquiries, not just likes\nFive signs your website is losing you leads", 300, false),
            $this->text('notebook_byline', 'Card 3: notebook byline (desktop only)', 'By the Ekdanta team', 60, false),
        ];
    }

    public function deviceDefaults(): array
    {
        return ['mobile' => [
            'insta_text' => 'Fresh reels and posts from our latest shoots.',
            'notebook_label' => 'NOTEBOOK',
        ]];
    }
}
