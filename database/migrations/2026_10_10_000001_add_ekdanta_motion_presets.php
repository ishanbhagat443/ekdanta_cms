<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The Ekdanta design's CSS @keyframes as Motion Library presets ("Ekdanta:
 * ekRig" → ek-rig), so the section pack's loops and load motion work on any
 * install and stay editable. Generated from the design files into
 * database/data/motion-presets-ekdanta.json. Slugs already present are left
 * alone; down() removes only the ones this added, if unchanged.
 */
return new class extends Migration
{
    private function presets(): array
    {
        return json_decode((string) file_get_contents(database_path('data/motion-presets-ekdanta.json')), true);
    }

    public function up(): void
    {
        $now = now();
        $existing = DB::table('motion_presets')->pluck('slug')->all();
        $rows = [];

        foreach ($this->presets() as $preset) {
            if (in_array($preset['slug'], $existing, true)) {
                continue;
            }
            $rows[] = [...$preset, 'keyframes' => json_encode($preset['keyframes']), 'params' => null, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now];
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('motion_presets')->insert($chunk);
        }
        Cache::forget('motion_presets');
    }

    public function down(): void
    {
        foreach ($this->presets() as $preset) {
            $row = DB::table('motion_presets')->where('slug', $preset['slug'])->where('is_system', true)->first();
            // The database stores JSON normalised: compare decoded, and only delete an unedited preset.
            if ($row && json_decode((string) $row->keyframes, true) == $preset['keyframes']) {
                DB::table('motion_presets')->where('id', $row->id)->delete();
            }
        }
        Cache::forget('motion_presets');
    }
};
