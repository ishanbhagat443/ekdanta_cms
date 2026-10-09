<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Motion Library: named, reusable motion (keyframes + default timing) that
 * a section's Motion tab tracks point at. Ships the core presets from
 * database/data/motion-presets.json: the 9 entrance effects sections used
 * before, the Hero Banner's entrance effects, scroll effects and loops.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motion_presets', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('kind', 10)->default('anime'); // anime (Anime.js keyframes) | css (@keyframes)
            $table->string('trigger', 10)->default('enter');
            $table->json('keyframes');
            $table->json('params')->nullable();
            $table->unsignedInteger('duration')->default(700);
            $table->unsignedInteger('delay')->default(0);
            $table->string('ease', 60)->default('outQuad');
            $table->unsignedInteger('stagger')->default(0);
            $table->string('description', 500)->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $now = now();
        $rows = array_map(fn (array $preset) => [
            ...$preset,
            'keyframes' => json_encode($preset['keyframes']),
            'params' => $preset['params'] === [] ? null : json_encode($preset['params']),
            'is_system' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], json_decode((string) file_get_contents(database_path('data/motion-presets.json')), true));

        DB::table('motion_presets')->insert($rows);
        Cache::forget('motion_presets'); // MotionPreset::CACHE_KEY: inserts here skip the model's own flush
    }

    public function down(): void
    {
        Schema::dropIfExists('motion_presets');
        Cache::forget('motion_presets');
    }
};
