<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\SectionMotion;
use Illuminate\Database\Eloquent\Model;

/**
 * One Motion Library preset: keyframes plus default timing, used by Motion
 * tab tracks. kind "anime" = Anime.js keyframes (any trigger, incl. scroll);
 * kind "css" = CSS @keyframes (time-based: load, loop, enter, hover, click,
 * state), with params passed in as CSS variables.
 */
class MotionPreset extends Model
{
    public const CACHE_KEY = 'motion_presets';

    public const KINDS = ['anime' => 'Anime.js keyframes (any trigger, including scroll)', 'css' => 'CSS @keyframes (time-based only)'];

    protected $fillable = ['name', 'slug', 'kind', 'trigger', 'keyframes', 'params', 'duration', 'delay', 'ease', 'stagger', 'description', 'is_system'];

    protected $casts = [
        'keyframes' => 'array',
        'params' => 'array',
        'duration' => 'integer',
        'delay' => 'integer',
        'stagger' => 'integer',
        'is_system' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => SectionMotion::forgetPresets());
        static::deleted(fn () => SectionMotion::forgetPresets());
    }
}
