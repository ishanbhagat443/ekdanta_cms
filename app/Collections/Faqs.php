<?php

declare(strict_types=1);

namespace App\Collections;

/** Questions and answers, grouped by page ("contact") or by a service's slug. */
class Faqs extends CollectionType
{
    public function key(): string { return 'faqs'; }
    public function label(): string { return 'FAQs'; }
    public function singular(): string { return 'FAQ'; }
    public function titleLabel(): string { return 'Question'; }
    public function icon(): string { return 'fas fa-question-circle'; }
    public function hint(): string { return 'Group "contact" feeds the Contact FAQ deck; a service slug feeds that service page.'; }

    public function fields(): array
    {
        return [
            $this->lines('answer', 'Answer', null, 1500),
            $this->text('group', 'Group (contact, or a service slug)', null, 120),
        ];
    }
}
