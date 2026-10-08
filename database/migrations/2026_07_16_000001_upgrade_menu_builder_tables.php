<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menus: a suggested placement (still just a label — any menu can be
        // assigned to any builder slot) + a handle for programmatic lookup.
        Schema::table('menus', function (Blueprint $table): void {
            $table->string('slug', 120)->nullable()->after('name');
            $table->string('location', 30)->nullable()->after('slug'); // header|footer|mobile|quick-links|sidebar|custom
            $table->text('description')->nullable()->after('location');
            $table->boolean('is_active')->default(true)->after('description');
        });

        // Menu items: richer link types, presentation, visibility and
        // responsive controls. Everything nullable/defaulted — additive.
        Schema::table('menu_items', function (Blueprint $table): void {
            // reference_id already existed (unsignedBigInteger, no FK — polymorphic
            // by convention). Pair it with an explicit model class for link types
            // page|product_category|product|blog|blog_category.
            $table->string('reference_type', 60)->nullable()->after('reference_id');

            // Navigation label can differ from the internal admin title.
            $table->string('nav_label', 100)->nullable()->after('label');
            $table->string('css_class', 150)->nullable()->after('icon');
            $table->string('element_id', 100)->nullable()->after('css_class');
            $table->string('badge_text', 40)->nullable()->after('element_id');
            $table->string('badge_color', 20)->nullable()->after('badge_text'); // bootstrap color key
            $table->string('permission', 100)->nullable()->after('badge_color'); // spatie permission name
            $table->boolean('auth_only')->default(false)->after('permission'); // logged-in only
            $table->boolean('guest_only')->default(false)->after('auth_only'); // guests only
            $table->unsignedTinyInteger('mega_columns')->default(4)->after('is_mega');
            $table->string('column_type', 20)->nullable()->after('mega_columns');
            // column_type (only meaningful on a mega-menu's direct children):
            //   links | image | featured_products | featured_categories | html | cta
            $table->foreignId('image_media_id')->nullable()->after('column_type')
                ->constrained('media')->nullOnDelete();
            $table->text('custom_html')->nullable()->after('image_media_id');
            $table->string('cta_label', 60)->nullable()->after('custom_html');
            $table->string('cta_url', 500)->nullable()->after('cta_label');
            $table->string('cta_style', 20)->nullable()->after('cta_url'); // primary|outline|link
            $table->unsignedTinyInteger('featured_limit')->default(4)->after('cta_style');
            $table->json('visible_devices')->nullable()->after('featured_limit'); // ["desktop","tablet","mobile"]
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('image_media_id');
            $table->dropColumn([
                'reference_type', 'nav_label', 'css_class', 'element_id', 'badge_text', 'badge_color',
                'permission', 'auth_only', 'guest_only', 'mega_columns', 'column_type',
                'custom_html', 'cta_label', 'cta_url', 'cta_style', 'featured_limit', 'visible_devices',
            ]);
        });

        Schema::table('menus', function (Blueprint $table): void {
            $table->dropColumn(['slug', 'location', 'description', 'is_active']);
        });
    }
};
