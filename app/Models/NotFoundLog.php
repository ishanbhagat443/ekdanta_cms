<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotFoundLog extends Model
{
    protected $fillable = ['path', 'referer', 'hits', 'resolved', 'last_hit_at'];

    protected $casts = [
        'resolved' => 'boolean',
        'last_hit_at' => 'datetime',
    ];

    /** Record (or bump the hit count on) a 404 for this path. */
    public static function record(string $path, ?string $referer): void
    {
        $existing = self::query()->where('path', $path)->first();

        if ($existing) {
            $existing->increment('hits');
            $existing->update(['last_hit_at' => now(), 'referer' => $referer ?: $existing->referer]);

            return;
        }

        self::create(['path' => $path, 'referer' => $referer, 'hits' => 1, 'last_hit_at' => now()]);
    }
}
