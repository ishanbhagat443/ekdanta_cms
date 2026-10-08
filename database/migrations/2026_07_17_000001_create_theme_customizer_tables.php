<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mirrors the `settings` table pattern: schema-less group/key/value
        // rows so new typography roles, color tokens, etc. are data, not
        // migrations. config/theme.php is the single source of truth for
        // what groups/keys/defaults/validation exist.
        Schema::create('theme_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 50)->index();
            $table->string('key', 100);
            $table->longText('value')->nullable();
            $table->string('type', 20)->default('text');
            $table->timestamps();

            $table->unique(['group', 'key']);
        });

        // Locally uploaded fonts (Typography → Local Font Upload). Each row
        // is one @font-face family, admin-named, files stored on the public
        // disk. Referenced by theme_settings typography.*_family = "local:{id}".
        Schema::create('theme_fonts', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('woff2_path', 255)->nullable();
            $table->string('woff_path', 255)->nullable();
            $table->string('ttf_path', 255)->nullable();
            $table->string('font_weight', 20)->default('400');
            $table->string('font_style', 20)->default('normal');
            $table->timestamps();
        });

        // Named snapshots of the full theme_settings table — used by
        // Export/Import/Backup and "Reset to Theme Default". A row with
        // is_system=true is the immutable factory-default snapshot seeded
        // on install; admin-made backups have is_system=false.
        Schema::create('theme_presets', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->json('payload'); // full [group.key => value] map at snapshot time
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_presets');
        Schema::dropIfExists('theme_fonts');
        Schema::dropIfExists('theme_settings');
    }
};
