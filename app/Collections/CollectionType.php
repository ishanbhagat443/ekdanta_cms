<?php

declare(strict_types=1);

namespace App\Collections;

use App\Models\CollectionItem;
use App\Support\FieldSchema;

/**
 * A kind of content kept as a list (case studies, services, client logos…).
 * It declares its fields like a section type does, so the admin form,
 * validation and storage all come from one schema.
 */
abstract class CollectionType
{
    use FieldSchema;

    /** URL-safe key, e.g. "case-studies". */
    abstract public function key(): string;

    /** Plural admin name. */
    abstract public function label(): string;

    abstract public function singular(): string;

    /** What the item's title is called in the form. */
    public function titleLabel(): string
    {
        return 'Name';
    }

    public function icon(): string
    {
        return 'fas fa-layer-group';
    }

    /** Address prefix of items with their own page ("/work"), or null. */
    public function pagePrefix(CollectionItem $item): ?string
    {
        return null;
    }

    /** The page setting "template for" value whose page shows one item. */
    public function template(): ?string
    {
        return null;
    }

    /** A line under the list explaining where the items appear. */
    public function hint(): string
    {
        return '';
    }
}
