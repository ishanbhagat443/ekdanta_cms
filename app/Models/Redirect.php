<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    public const CACHE_KEY = 'seo.redirects.map';

    protected $fillable = ['from_path', 'to_url', 'status_code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_hit_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Any change invalidates the middleware's cached lookup map.
        static::saved(function (self $redirect): void {
            Cache::forget(self::CACHE_KEY);

            // 404 Manager: a redirect now covers this path — auto-resolve it.
            NotFoundLog::where('path', $redirect->from_path)->update(['resolved' => true]);
        });
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** "/old-page/" and "old-page" both normalise to "/old-page". */
    public static function normalisePath(string $path): string
    {
        $path = '/'.trim($path);
        $path = '/'.ltrim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /** @return array<string, array{id: int, to: string, code: int}> keyed by from_path */
    public static function activeMap(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => self::query()
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(fn (self $r) => [$r->from_path => ['id' => $r->id, 'to' => $r->to_url, 'code' => $r->status_code]])
            ->all());
    }
}
