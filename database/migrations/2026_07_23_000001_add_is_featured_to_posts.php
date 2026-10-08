<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog CMS integration (Designs/Blog Listing + Blog Detail): a single
 * additive column. Everything else the brief asks for (title, featured
 * image, gallery, author, publish date, category, tags, related posts,
 * SEO) is already fully CMS-editable via the existing Post admin form —
 * only "Featured Status" (detail) / "Featured First" (grid sort) needed a
 * new flag. Reading Time is computed from the body word count at render
 * time (Post::readingTimeMinutes()) — no column needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->boolean('is_featured')->default(false)->after('status');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex(['is_featured']);
            $table->dropColumn('is_featured');
        });
    }
};
