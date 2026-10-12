<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * 404 page: the two 4s drop in and the Ekdanta "e" rolls in as the 0, then
 * looks around; scrolling pulls them apart (design: Not-Found n-top).
 */
class NotFoundHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'not-found-hero';
    }

    protected function anchor(): string
    {
        return 'n-top';
    }

    public function label(): string
    {
        return 'Ekdanta · 404 Hero';
    }

    protected function content(): array
    {
        return [
            $this->text('code', 'Big number (the middle digit is the logo)', '404', 3, false),
            $this->image('mark', 'Logo that rolls in as the middle digit', 'ekdanta/e-mark'),
            $this->text('badge', 'Small line above the heading', 'ERROR 404 · PAGE NOT FOUND', 60),
            $this->text('heading', 'Heading, start', 'Arre! Yeh page', 60),
            $this->text('heading_2', 'Heading, green word', 'gayab', 30),
            $this->text('heading_3', 'Heading, end', 'hai.', 30),
            $this->lines('text', 'Text', "The link may be broken, or the page may have moved. Let's get you back on track.", 300),
            $this->text('button_label', 'Main button', 'Take me home', 40),
            $this->link('button_link', 'Main button link', '/'),
            $this->text('button_2_label', 'Second button', 'See our services', 40),
            $this->link('button_2_link', 'Second button link', '/services'),
            $this->text('search_label', 'Search link', 'Search the site', 40),
            $this->link('search_link', 'Search link goes to', '#n-routes'),
        ];
    }
}
