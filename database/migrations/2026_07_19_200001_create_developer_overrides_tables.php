<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Developer Mode override system: per-section HTML/CSS/JS overrides stored
 * SEPARATELY from the original Blade components (never overwritten), with
 * full version history for restore.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developer_overrides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('section_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('html')->nullable();
            $table->text('css')->nullable();
            $table->text('js')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('developer_override_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('developer_override_id')->constrained()->cascadeOnDelete();
            $table->longText('html')->nullable();
            $table->text('css')->nullable();
            $table->text('js')->nullable();
            $table->string('label', 60)->nullable(); // e.g. "before reset"
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_override_versions');
        Schema::dropIfExists('developer_overrides');
    }
};
