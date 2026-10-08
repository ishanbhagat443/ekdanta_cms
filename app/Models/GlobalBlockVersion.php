<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalBlockVersion extends Model
{
    protected $fillable = [
        'global_block_id',
        'version',
        'data_desktop',
        'data_tablet',
        'data_mobile',
        'style',
        'created_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'data_desktop' => 'array',
        'data_tablet' => 'array',
        'data_mobile' => 'array',
        'style' => 'array',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(GlobalBlock::class, 'global_block_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
