<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 404 Manager: every real 404 hit on the public site is logged here
        // (deduped by path) so an admin can review broken links and convert
        // any of them into a Redirect with one click.
        Schema::create('not_found_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('path', 191)->unique();
            $table->string('referer', 500)->nullable();
            $table->unsignedInteger('hits')->default(1);
            $table->boolean('resolved')->default(false); // true once a redirect exists for it
            $table->timestamp('last_hit_at');
            $table->timestamps();

            $table->index(['resolved', 'last_hit_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('not_found_logs');
    }
};
