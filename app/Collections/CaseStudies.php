<?php

declare(strict_types=1);

namespace App\Collections;

use App\Models\CollectionItem;

/** Case studies: Home work showcase, Services proof, Work page, and each one's own page at /work/{slug}. */
class CaseStudies extends CollectionType
{
    public function key(): string { return 'case-studies'; }
    public function label(): string { return 'Case studies'; }
    public function singular(): string { return 'Case study'; }
    public function icon(): string { return 'fas fa-briefcase'; }
    public function template(): ?string { return 'case_study'; }
    public function pagePrefix(CollectionItem $item): ?string { return '/work'; }
    public function hint(): string { return 'Shown on Home (work showcase), Services (proof) and Work; each opens at /work/its-slug through the page marked "Template for: Case study".'; }

    public function fields(): array
    {
        return [
            $this->text('short_name', 'Short name (headings, e.g. "SRK Dairy")', null, 80),
            $this->text('sector', 'Sector label (capitals, e.g. INFRASTRUCTURE AND PRECAST)', null, 80),
            $this->text('industry', 'Industry (sentence case)', null, 80),
            $this->colour('colour', 'Brand colour'),
            $this->image('logo', 'Client logo'),
            $this->image('image', 'Instagram grid image'),
            $this->link('instagram_url', 'Instagram profile link'),
            $this->text('reels', 'Reels (slugs from Reels, comma separated)', null, 300),
            $this->lines('description', 'Description', null, 600),
            $this->lines('brief', 'The brief', null, 800),
            $this->lines('approach', 'Our approach', null, 800),
            $this->lines('made', 'What we made (one per line)', null, 600),
            $this->rows('facts', 'Facts', [$this->text('value', 'Number or word'), $this->text('label', 'What it means')], [], 4),
            $this->text('home_result', 'Result number (Home)', null, 120),
            $this->text('result_line', 'Result in one line'),
            $this->rows('results', 'Results', [$this->text('label', 'Measure'), $this->text('value', 'Number'), $this->text('note', 'Note')], [], 3),
            $this->lines('quote', 'Client quote', null, 800),
            $this->text('quote_name', 'Quote: name', null, 100),
            $this->text('quote_role', 'Quote: role', null, 100),
            $this->text('next', 'Next case study (slug; empty = the next in this list)', null, 120),
        ];
    }
}
