<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A Global Block is a reusable, versioned, named TEMPLATE for one of
        // the existing SectionType payloads (hero/faq/testimonials/etc — see
        // app/Services/SectionTypes/). It stores its own copy of the
        // desktop/tablet/mobile/style JSON in exactly the same shape a
        // Section does, so the existing render pipeline, validation and
        // responsive/device-override logic are reused as-is — no second
        // rendering engine, no new field types.
        Schema::create('global_block_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('global_blocks', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->foreignId('category_id')->nullable()->constrained('global_block_categories')->nullOnDelete();
            $table->string('type', 50); // SectionType key — see SectionTypeRegistry
            $table->foreignId('preview_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active'); // active|draft|archived
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('data_desktop')->nullable();
            $table->json('data_tablet')->nullable();
            $table->json('data_mobile')->nullable();
            $table->json('style')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('use_count')->default(0); // "Most Used" — incremented when placed on a page
            $table->timestamp('last_used_at')->nullable(); // "Recently Used"
            $table->timestamps();

            $table->index(['status', 'type']);
        });

        // Version History — a snapshot is written every time the master
        // payload changes (not on every autosave-tick, on explicit save).
        Schema::create('global_block_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('global_block_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('data_desktop')->nullable();
            $table->json('data_tablet')->nullable();
            $table->json('data_mobile')->nullable();
            $table->json('style')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['global_block_id', 'version']);
        });

        // Link an on-page Section instance back to its master. NULL = a
        // plain, always-local section (unchanged existing behaviour).
        // is_synced=true means "Update All Pages" pushes the master payload
        // here on every master save; false ("Detach") means this row keeps
        // its own copy going forward even though it remembers where it came
        // from (so re-attaching / auditing "used on N pages" stays possible).
        Schema::table('sections', function (Blueprint $table): void {
            $table->foreignId('global_block_id')->nullable()->after('type')
                ->constrained('global_blocks')->nullOnDelete();
            $table->boolean('is_synced')->default(true)->after('global_block_id');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('global_block_id');
            $table->dropColumn('is_synced');
        });

        Schema::dropIfExists('global_block_versions');
        Schema::dropIfExists('global_blocks');
        Schema::dropIfExists('global_block_categories');
    }
};
