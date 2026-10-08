<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaFolder extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
    }

    /** Breadcrumb trail: root … this folder. */
    public function trail(): array
    {
        $trail = [$this];
        $node = $this;

        while ($node->parent !== null) {
            $node = $node->parent;
            array_unshift($trail, $node);
        }

        return $trail;
    }
}
