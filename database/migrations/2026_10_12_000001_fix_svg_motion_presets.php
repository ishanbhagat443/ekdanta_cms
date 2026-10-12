<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Eight Ekdanta presets move parts inside small SVG icons. Inside an SVG a
 * px is one viewBox unit, so their distances must stay plain px; they were
 * imported as calc(N * var(--u)), right only at the design size. Updates
 * only rows still exactly as imported; down() undoes only unedited rows.
 */
return new class extends Migration
{
    private const SLUGS = ['ek-n-bob', 'ek-n-pen', 'ek-n-smoke', 'ek-n-stack', 'ek-p-box', 'ek-p-truck', 'ek-t-env', 'ek-t-scan'];

    public function up(): void
    {
        $this->swap(fn (string $json) => preg_replace('/calc\((-?[\d.]+) \* var\(--u\)\)/', '$1px', $json));
    }

    public function down(): void
    {
        $this->swap(fn (string $json) => preg_replace('/(?<![\w.])(-?[\d.]+)px/', 'calc($1 * var(--u))', $json));
    }

    /** Rewrites each unedited row: it must turn into exactly the other version. */
    private function swap(callable $convert): void
    {
        $shipped = collect(json_decode((string) file_get_contents(database_path('data/motion-presets-ekdanta.json')), true))
            ->keyBy('slug');

        foreach (self::SLUGS as $slug) {
            $row = DB::table('motion_presets')->where('slug', $slug)->where('is_system', true)->first();
            $fixed = $shipped[$slug]['keyframes'] ?? null;
            if (! $row || $fixed === null) {
                continue;
            }
            $stored = json_decode((string) $row->keyframes, true);
            $next = json_decode($convert(json_encode($stored, JSON_UNESCAPED_SLASHES)), true);
            // Up: the stored row must convert into the shipped fix. Down: the stored row must BE the fix.
            $unedited = str_contains((string) $row->keyframes, 'var(--u)') ? $next == $fixed : $stored == $fixed;
            if ($unedited && $next != $stored) {
                DB::table('motion_presets')->where('id', $row->id)->update(['keyframes' => json_encode($next, JSON_UNESCAPED_SLASHES), 'updated_at' => now()]);
            }
        }
        Cache::forget('motion_presets');
    }
};
