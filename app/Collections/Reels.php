<?php

declare(strict_types=1);

namespace App\Collections;

/** Reels: Home reels carousel, Work reels arc. */
class Reels extends CollectionType
{
    public function key(): string { return 'reels'; }
    public function label(): string { return 'Reels'; }
    public function singular(): string { return 'Reel'; }
    public function titleLabel(): string { return 'Title'; }
    public function icon(): string { return 'fas fa-film'; }

    public function fields(): array
    {
        return [
            $this->text('brand', 'Brand', null, 120),
            $this->text('tag', 'Tag (capitals, e.g. SKINCARE)', null, 60),
            $this->image('cover', 'Cover image'),
            $this->link('instagram_url', 'Instagram link'),
            $this->text('views', 'Views (e.g. 12.4K)', null, 30),
        ];
    }
}
