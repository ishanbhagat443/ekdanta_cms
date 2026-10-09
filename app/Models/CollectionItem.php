<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\CollectionRegistry;
use App\Collections\CollectionType;
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

    public function type(): CollectionType
    {
        return app(CollectionRegistry::class)->find($this->collection);
    }

    /** A field value, falling back to its default. */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /** The public address of an item with its own page, or null. */
    public function url(): ?string
    {
        $prefix = $this->type()->pagePrefix($this);

        return $prefix === null ? null : url($prefix.'/'.$this->slug);
    }
}
