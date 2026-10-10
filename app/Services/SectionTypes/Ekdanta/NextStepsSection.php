<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Thank-you page: what happens next. The visitor's enquiry travels along
 * the track step by step; each step lights up as it is reached (design:
 * Thank-You t-next).
 */
class NextStepsSection extends EkdantaSection
{
    public const ICONS = ['mail' => 'Envelope', 'phone' => 'Phone', 'search' => 'Magnifier', 'doc' => 'Document'];

    protected function slug(): string
    {
        return 'next-steps';
    }

    protected function anchor(): string
    {
        return 't-next';
    }

    public function label(): string
    {
        return 'Ekdanta · Next Steps Track';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'WHAT HAPPENS NEXT', 40),
            $this->text('heading', 'Heading', 'Ab aage kya hoga?', 80),
            $this->lines('text', 'Text beside the heading (desktop)', 'Four simple steps, from your message to a plan made for your business.', 300),
            $this->text('marker', 'Marker on the track', 'Your enquiry', 40),
            $this->text('marker_done', 'Marker on the last step', 'Plan on its way', 40),
            $this->text('progress', 'Phones: line under the heading ({n} = the step, {total} = how many)', 'Your enquiry is at step {n} of {total}', 80),
            $this->text('progress_done', 'Phones: line on the last step', 'Your plan is on its way', 80),
            $this->rows('steps', 'Steps', [
                $this->text('kicker', 'Small label (e.g. STEP 1 · NOW)', null, 40),
                $this->text('title', 'Title', null, 80),
                $this->lines('text', 'Text', null, 300),
                $this->text('chip', 'Chip', null, 40),
                $this->select('icon', 'Icon', self::ICONS),
            ], [
                ['kicker' => 'STEP 1 · NOW', 'title' => 'Your message is with us', 'text' => 'It has reached our team in Surat. There is nothing more for you to fill in.', 'chip' => 'Done', 'icon' => 'mail'],
                ['kicker' => 'STEP 2', 'title' => 'We call you', 'text' => 'A short call to understand your business, your products and what you want to grow.', 'chip' => '[Add reply time]', 'icon' => 'phone'],
                ['kicker' => 'STEP 3', 'title' => 'We study your brand', 'text' => 'We look at your posts, your website and your competitors before we suggest anything.', 'chip' => 'Before we meet', 'icon' => 'search'],
                ['kicker' => 'STEP 4', 'title' => 'You get a plan and a quote', 'text' => 'Made for your business, so you only pay for what you need. No fixed packages.', 'chip' => 'Made for you', 'icon' => 'doc'],
            ], 4),
        ];
    }
}
