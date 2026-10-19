<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Services: how we work. The path draws itself, the Ekdanta mark rides its
 * tip and each step pops in as it is reached (design: Services s-process).
 * The path is drawn for five steps.
 */
class ProcessPathSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'process-path';
    }

    protected function anchor(): string
    {
        return 's-process';
    }

    public function label(): string
    {
        return 'Ekdanta · Process Path';
    }

    protected function content(): array
    {
        return [
            $this->text('eyebrow', 'Small line above the heading', 'HOW WE WORK', 40),
            $this->text('heading', 'Heading, line 1', 'Pehle samjhenge,', 80),
            $this->text('heading_2', 'Heading, line 2 (green)', 'phir banayenge.', 80),
            $this->lines('text', 'Text beside the heading (desktop)', 'First we understand your business, then we build. The same five steps for every brand, big or small.', 300),
            $this->rows('steps', 'Steps (the path has five)', [
                $this->text('title', 'Step', null, 60),
                $this->lines('text', 'Text', null, 300),
            ], [
                ['title' => 'Free consultation', 'text' => 'A call or office visit to understand your business, customers and goals.'],
                ['title' => 'Audit and strategy', 'text' => 'We study your brand, competitors and current marketing, then decide what to fix first.'],
                ['title' => 'Custom plan and quote', 'text' => 'A clear monthly plan and quote, built around what you need. No fixed packages.'],
                ['title' => 'Create and launch', 'text' => 'Our in-house team designs, writes, shoots and builds, then launches with you.'],
                ['title' => 'Report and grow', 'text' => 'Monthly reports and a review call. We keep what works and improve the rest.'],
            ], 5),
            $this->image('mark', 'Mark riding the path', 'ekdanta/e-mark'),
        ];
    }

    /** Screen fit: parts its automatic rule places wrongly (see SectionType::fitParts). */
    public function fitParts(): array
    {
        return [
            '.ek-comp--d .ek-art > div:nth-child(5)' => 'L',
        ];
    }
}
