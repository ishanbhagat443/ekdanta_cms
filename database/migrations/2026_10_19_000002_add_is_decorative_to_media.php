<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media Library → "Decorative": a picture that only decorates (a mark next
 * to text, a drawing's layer) has no description; screen readers skip it,
 * and it isn't flagged as missing one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->boolean('is_decorative')->default(false)->after('alt');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropColumn('is_decorative');
        });
    }
};
