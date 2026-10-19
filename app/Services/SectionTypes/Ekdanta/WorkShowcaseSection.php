<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

/**
 * Home: the case studies gather, the first comes forward, then each case
 * slides in with its brand colour (design: j-work / m-work). Cases come
 * from Collections → Case studies.
 */
class WorkShowcaseSection extends EkdantaSection
{
    protected function slug(): string
    {
        return 'work-showcase';
    }

    protected function anchor(): string
    {
        return 'j-work';
    }

    public function label(): string
    {
        return 'Ekdanta · Work Showcase';
    }

    protected function content(): array
    {
        return [
            $this->text('heading', 'Heading', 'हमारा काम ही हमारी पहचान है।', 120),
            $this->text('subheading', 'Line under the heading', 'Our work is our identity. 45+ brands, and a few of their stories.', 200),
            $this->text('made_label', '"What we made" label', 'WHAT WE MADE', 40),
            $this->text('result_label', '"Result" label', 'RESULT', 40),
            $this->text('image_alt', 'Image description ({name} = the client)', 'Social media work for {name}', 120),
            $this->text('cases', 'Case studies to show (slugs, comma separated; empty = all, in their order)', null, 600, false),
            $this->colour('base_colour', 'Background before the first case', '#0A5A30'),
        ];
    }

    /** Screen fit: parts its automatic rule places wrongly (see SectionType::fitParts). */
    public function fitParts(): array
    {
        return [
            '.ek-comp--d [data-m="work-panels"]' => 'R',
        ];
    }
}
