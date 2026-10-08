<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Website-builder design system: reusable Gradient Library + Button Builder.
 * Both are config-as-JSON (mirrors the sections engine) so new options never
 * need schema changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gradients', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('description', 500)->nullable();
            $table->string('type', 10)->default('linear'); // linear | radial | conic
            $table->unsignedSmallInteger('angle')->default(90); // deg (linear/conic)
            $table->string('position', 60)->nullable(); // radial/conic center, e.g. "center", "50% 0%"
            $table->json('stops'); // [{color, opacity(0-100), position(0-100)}, ...] unlimited
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('button_styles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true);
            // Full style definition: typography, per-device padding, border,
            // per-corner radius, background (solid/gradient/image/video/
            // transparent), hover state, animation, icons, shadows.
            $table->json('config');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('button_styles');
        Schema::dropIfExists('gradients');
    }
};
