<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The legal pages' CSS @keyframes (the Privacy and Terms boards) as Motion
 * Library presets (ek-lg-*), from database/data/motion-presets-legal.json.
 * Slugs already present are left alone; down() removes only the ones this
 * added, if unchanged.
 */
return new class extends Migration
{
    private function presets(): array
    {
        return json_decode((string) file_get_contents(database_path('data/motion-presets-legal.json')), true);
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

        DB::table('motion_presets')->insert($rows);
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
