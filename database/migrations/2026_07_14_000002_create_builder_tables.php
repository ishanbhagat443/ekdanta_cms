<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Global layout areas: header, topbar, footer. One row per area,
        // each owning an ordered set of sections (polymorphic).
        Schema::create('layouts', function (Blueprint $table) {
            $table->id();
            $table->string('area', 20)->unique(); // header | footer | topbar
            $table->json('settings')->nullable(); // sticky, container, etc.
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->timestamps();
        });

        // Builder sections. Owned by a Layout now, later also by Pages.
        // Device columns: desktop = full payload, tablet/mobile = sparse
        // overrides merged over desktop at render (inheritance rule).
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->morphs('sectionable'); // sectionable_type + sectionable_id (+index)
            $table->string('type', 50);
            $table->string('name')->nullable(); // admin label
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('data_desktop')->nullable();
            $table->json('data_tablet')->nullable();
            $table->json('data_mobile')->nullable();
            $table->json('style')->nullable(); // per-device visibility/spacing/bg
            $table->timestamps();

            $table->index(['sectionable_type', 'sectionable_id', 'sort_order'], 'sections_owner_order_idx');
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('label', 100);
            $table->string('link_type', 20)->default('url'); // url | page | category … (future)
            $table->string('url')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable(); // future: page/category id
            $table->string('target', 10)->default('_self');
            $table->string('icon', 50)->nullable();
            $table->boolean('is_mega')->default(false); // children render as mega-menu columns
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('layouts');
    }
};
