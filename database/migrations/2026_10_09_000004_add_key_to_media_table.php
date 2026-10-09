<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A stable name for a media file ("ekdanta/logo-colour"), so section
 * defaults and exports point at the file itself, not at a database id that
 * differs between servers. Optional: uploads keep working without one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->string('key', 120)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropUnique(['key']);
            $table->dropColumn('key');
        });
    }
};
