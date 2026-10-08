<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemePreset extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'payload',
        'is_system',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_system' => 'boolean',
    ];
}
