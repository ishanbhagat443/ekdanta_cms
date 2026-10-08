<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlobalBlockCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function blocks(): HasMany
    {
        return $this->hasMany(GlobalBlock::class, 'category_id');
    }
}
