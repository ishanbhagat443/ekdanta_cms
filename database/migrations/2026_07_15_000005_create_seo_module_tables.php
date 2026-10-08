<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enterprise SEO: Twitter Card, breadcrumb override and sitemap
        // controls on the existing polymorphic seo_meta row.
        Schema::table('seo_meta', function (Blueprint $table): void {
            $table->string('twitter_card', 30)->nullable()->after('schema_json'); // summary|summary_large_image
            $table->string('twitter_title')->nullable()->after('twitter_card');
            $table->text('twitter_description')->nullable()->after('twitter_title');
            $table->foreignId('twitter_media_id')->nullable()->after('twitter_description')
                ->constrained('media')->nullOnDelete();
            $table->string('breadcrumb_title')->nullable()->after('twitter_media_id');
            $table->boolean('sitemap_include')->default(true)->after('breadcrumb_title');
            $table->string('sitemap_priority', 3)->nullable()->after('sitemap_include'); // "0.1".."1.0"
            $table->string('sitemap_changefreq', 10)->nullable()->after('sitemap_priority');
        });

        // 301/302 redirects, matched on exact request path.
        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('from_path', 191)->unique(); // "/old-page"
            $table->string('to_url', 500); // "/new-page" or absolute URL
            $table->unsignedSmallInteger('status_code')->default(301); // 301|302
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');

        Schema::table('seo_meta', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('twitter_media_id');
            $table->dropColumn([
                'twitter_card', 'twitter_title', 'twitter_description',
                'breadcrumb_title', 'sitemap_include', 'sitemap_priority', 'sitemap_changefreq',
            ]);
        });
    }
};
