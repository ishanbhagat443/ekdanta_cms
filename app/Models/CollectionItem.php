<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\CollectionRegistry;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One item of a collection (a case study, a service, a client logo…). */
class CollectionItem extends Model
{
    use HasSeoMeta;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = ['collection', 'slug', 'title', 'status', 'sort_order', 'data'];

    protected $casts = ['data' => 'array', 'sort_order' => 'integer'];

    public function scopeOf(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Published items for a section: the slugs listed (comma separated, in
     * that order), or every published item in the collection's order.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function pick(string $collection, ?string $slugs = null): \Illuminate\Support\Collection
    {
        $items = self::query()->of($collection)->published()->get();
        $wanted = array_values(array_filter(array_map('trim', explode(',', (string) $slugs))));

        return $wanted === []
            ? $items
            : collect($wanted)->map(fn ($slug) => $items->firstWhere('slug', $slug))->filter()->values();
    }

    /** A field value, falling back to its default. */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /** The public address of an item with its own page, or null. */
    public function url(): ?string
    {
        $prefix = app(CollectionRegistry::class)->find($this->collection)->pagePrefix($this);

        return $prefix === null ? null : url($prefix.'/'.$this->slug);
    }
}
