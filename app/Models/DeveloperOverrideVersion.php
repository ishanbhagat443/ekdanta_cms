<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperOverrideVersion extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['developer_override_id', 'html', 'css', 'js', 'label', 'created_by', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function override(): BelongsTo
    {
        return $this->belongsTo(DeveloperOverride::class, 'developer_override_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
