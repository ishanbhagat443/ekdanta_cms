<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 191)->unique();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug', 191)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // author account
            $table->string('author_name', 100)->nullable(); // display override, e.g. the brand name
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status', 20)->default('draft'); // draft | published | scheduled
            $table->timestamp('published_at')->nullable();  // drives scheduling + archives
            $table->unsignedBigInteger('views_count')->default(0); // popular posts
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('views_count');
        });

        Schema::create('blog_category_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->unique(['blog_category_id', 'post_id']);
        });

        Schema::create('blog_tag_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->unique(['blog_tag_id', 'post_id']);
        });

        Schema::create('related_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('related_post_id')->constrained('posts')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['post_id', 'related_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_posts');
        Schema::dropIfExists('blog_tag_post');
        Schema::dropIfExists('blog_category_post');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
    }
};
