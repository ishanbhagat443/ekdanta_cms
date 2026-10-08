<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shared, module-agnostic tables: polymorphic SEO meta (pages, posts …) and
 * ordered polymorphic media attachments (galleries, downloads).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('metable');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots', 50)->nullable(); // e.g. "noindex,nofollow"
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->foreignId('og_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->longText('schema_json')->nullable();
            $table->timestamps();

            $table->unique(['metable_type', 'metable_id']);
        });

        Schema::create('mediables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->morphs('mediable');
            $table->string('collection', 30)->default('gallery');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['media_id', 'mediable_type', 'mediable_id', 'collection'], 'mediables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediables');
        Schema::dropIfExists('seo_meta');
    }
};
