<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * pages.settings: per-page JSON settings store, so a screen's options
 * (template blocks, blog grid/sidebar config, ...) are managed from the
 * Pages module instead of a global settings group.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->json('settings')->nullable()->after('banner_media_id');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn('settings');
        });
    }
};
