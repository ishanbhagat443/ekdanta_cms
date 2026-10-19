<?php

declare(strict_types=1);

namespace App\Services\SectionTypes\Ekdanta;

use App\Support\LegalContent;

/**
 * Privacy Policy and Terms of Service: the full text with its reading rail
 * (design: Privacy l-full, Terms t-full). The Article Body's rail and
 * script: each heading is numbered and listed under "On this page"; on wide
 * screens the rail stays beside the text with the reading progress; on
 * phones and tablets the list sits on top and a slim bar under the header
 * shows the part you are reading. Drawn once for every screen size, so
 * its fields have no phone values (as the Article Body).
 */
class LegalBodySection extends EkdantaSection
{
    public const BLOCK_TYPES = ['heading' => 'Heading (starts a numbered part)', 'paragraph' => 'Paragraph', 'list' => 'List (one item per line)',
        'note' => 'Highlighted note', 'labels' => 'Label and value rows (one per line: label | value)'];

    protected function slug(): string
    {
        return 'legal-body';
    }

    protected function anchor(): string
    {
        return 'l-full';
    }

    public function label(): string
    {
        return 'Ekdanta · Legal Body + Reading Rail';
    }

    protected function content(): array
    {
        $d = LegalContent::sections('privacy-policy')['ekdanta-legal-body'];

        return [
            $this->rows('blocks', 'The text, block by block', [
                $this->select('type', 'Block', self::BLOCK_TYPES, 'paragraph'),
                $this->lines('text', 'Text', null, 3000, false),
            ], $d['blocks'], 120),
            $this->text('toc_label', 'Contents label', $d['toc_label'], 40, false),
            $this->text('left_label', 'Rail: time left ({minutes})', '{minutes} min left', 40, false),
            $this->text('done_label', 'Rail: at the end', 'Done reading', 40, false),
            $this->text('read_label', 'Rail: progress ({percent})', '{percent}% read', 40, false),
            $this->text('ask_before', 'Rail: question line', $d['ask_before'], 60, false),
            $this->text('ask_label', 'Rail: question link text', $d['ask_label'], 60, false),
            $this->link('ask_link', 'Rail: question link goes to', $d['ask_link']),
            $this->text('end_note', 'Line under the text', $d['end_note'], 120, false),
        ];
    }
}
