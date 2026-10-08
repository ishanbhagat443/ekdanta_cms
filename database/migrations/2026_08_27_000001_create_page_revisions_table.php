<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Version history for pages.
 *
 * One row per explicit save: a JSON snapshot of the page's own fields plus
 * every section payload, so a page can be rolled back to exactly how it
 * looked. Autosaves deliberately do NOT write here — they fire every couple
 * of seconds and would bury the handful of snapshots a user actually wants.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('label')->nullable(); // e.g. "Before restore", "Published"
            $table->json('data');
            $table->timestamps();

            // The history panel always reads "newest first for this page".
            $table->index(['page_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
    }
};
