<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Work: the hero. An isometric wall of real work (case study and reel
 * pictures from Collections) drifts on its own; scrolling pulls it closer
 * (design: Work w-top).
 */
class WorkHeroSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'work-hero';
    }

    protected function anchor(): string
    {
        return 'w-top';
    }

    public function label(): string
    {
        return 'Ekdanta · Work Hero (Wall)';
    }

    protected function content(): array
    {
        return [
            $this->text('crumb_home', 'Breadcrumb: first link', 'Home', 40),
            $this->link('crumb_home_link', 'Breadcrumb: first link goes to', '/'),
            $this->text('crumb_current', 'Breadcrumb: this page', 'Work', 60),
            $this->text('heading', 'Heading, line 1', 'Kaam', 40),
            $this->text('heading_2', 'Heading, line 2 (green)', 'bolta hai.', 40),
            $this->lines('text', 'Text', '45+ brands across manufacturing, food, retail and services. Here is some of what we have made for them.', 300),
            $this->rows('chips', 'Small facts under the text', [
                $this->text('number', 'Number (green)', null, 10),
                $this->text('label', 'Label', null, 30),
            ], [
                ['number' => '6', 'label' => 'case studies'],
                ['number' => '45+', 'label' => 'brands'],
                ['number' => '4', 'label' => 'industries'],
            ], 3),
            $this->text('button_label', 'Main button', 'See the case studies', 40),
            $this->link('button_link', 'Main button link', '#w-cases'),
            $this->text('button_2_label', 'Second button (with a play icon)', 'Watch the reels', 40),
            $this->link('button_2_link', 'Second button link', '#w-reels'),
            $this->text('hint', 'Scroll hint', 'SCROLL TO EXPLORE', 60),
            $this->text('cases', 'Wall: case studies (slugs, comma separated; empty = all)', '', 400, false),
            $this->text('reels', 'Wall: reels (slugs, comma separated; empty = all)', '', 400, false),
        ];
    }
}
