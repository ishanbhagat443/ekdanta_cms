<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Two About presets were imported with only their end frame: the design
 * set the start (no highlight, no handwriting) in a class. Sections draw
 * the finished state, so each preset now carries its own start. Updates
 * only rows still exactly as imported; down() undoes only unedited rows.
 */
return new class extends Migration
{
    private const CHANGES = [
        'ek-ab-hl' => [
            ['to' => ['background-size' => '100% 100%']],
            ['from' => ['background-size' => '0% 100%'], 'to' => ['background-size' => '100% 100%']],
        ],
        'ek-ab-write' => [
            ['to' => ['max-width' => 'calc(240 * var(--u))']],
            ['from' => ['max-width' => '0'], 'to' => ['max-width' => 'calc(240 * var(--u))']],
        ],
    ];

    public function up(): void
    {
        $this->swap(0, 1);
    }

    public function down(): void
    {
        $this->swap(1, 0);
    }

    private function swap(int $from, int $to): void
    {
        foreach (self::CHANGES as $slug => $versions) {
            $row = DB::table('motion_presets')->where('slug', $slug)->where('is_system', true)->first();
            if ($row && json_decode((string) $row->keyframes, true) == $versions[$from]) {
                DB::table('motion_presets')->where('id', $row->id)->update(['keyframes' => json_encode($versions[$to], JSON_UNESCAPED_SLASHES), 'updated_at' => now()]);
            }
        }
        Cache::forget('motion_presets');
    }
};
