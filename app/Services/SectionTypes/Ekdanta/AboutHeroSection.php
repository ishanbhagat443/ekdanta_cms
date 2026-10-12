<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * About: the dictionary entry for "Ekdanta" writes itself while a red
 * ledger (khata) opens and ticks off its lines (design: About a-top).
 */
class AboutHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'about-hero';
    }

    protected function anchor(): string
    {
        return 'a-top';
    }

    public function label(): string
    {
        return 'Ekdanta · About Hero (Dictionary)';
    }

    protected function content(): array
    {
        return [
            $this->text('crumb_home', 'Breadcrumb: first link', 'Home', 40),
            $this->link('crumb_home_link', 'Breadcrumb: first link goes to', '/'),
            $this->text('crumb_current', 'Breadcrumb: this page', 'About us', 60),
            $this->text('word', 'The word (heading)', 'Ekdanta', 20, false),
            $this->number('word_green', 'How many first letters are green', 2, 0, 20),
            $this->text('script', 'In Devanagari', 'एकदन्त', 40, false),
            $this->text('pronunciation', 'How to say it', 'ek-dun-tuh', 40, false),
            $this->text('word_class', 'Word class', 'noun', 20, false),
            $this->lines('meanings', 'Meanings, one per line (the last one is highlighted)', "The one-tusked. A name of Lord Ganesha, remembered before every new beginning.\nVighnaharta, the remover of obstacles.\nA branding and marketing team from Surat that removes the obstacles between your business and its customers.", 800),
            $this->text('button_label', 'Main button', 'Read our story', 40),
            $this->link('button_link', 'Main button link', '#a-story'),
            $this->text('button_2_label', 'Second button', 'Work with us', 40),
            $this->link('button_2_link', 'Second button link', '/contact'),
            $this->text('hint', 'Scroll hint', 'SCROLL TO EXPLORE', 60),
            $this->text('ledger_title', 'Ledger: title', 'Ekdanta khata', 40, false),
            $this->text('ledger_page', 'Ledger: page number', 'पान १', 20, false),
            $this->rows('ledger', 'Ledger: lines (a ✓ mark is green, anything else red)', [
                $this->text('text', 'Line', null, 30),
                $this->text('mark', 'Mark', null, 10),
            ], [
                ['text' => 'Naya brand', 'mark' => '✓'],
                ['text' => 'Naye leads', 'mark' => '✓'],
                ['text' => 'Badhti sales', 'mark' => '✓'],
                ['text' => 'Big brand', 'mark' => 'next!'],
            ], 4),
            $this->text('cover_line', 'Ledger cover', '॥ शुभ लाभ ॥', 40, false),
            $this->text('inside_line', 'Inside the cover: top line', '॥ श्री गणेशाय नमः ॥', 40, false),
            $this->text('inside_left', 'Inside the cover: left word', 'शुभ', 20, false),
            $this->text('inside_right', 'Inside the cover: right word', 'लाभ', 20, false),
            $this->text('inside_name', 'Inside the cover: name', 'Ekdanta', 30, false),
            $this->text('inside_place', 'Inside the cover: places', 'Surat · Ahmedabad', 60, false),
            $this->image('mark', 'Ledger: logo', 'ekdanta/e-mark'),
        ];
    }
}
