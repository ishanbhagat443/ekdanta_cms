<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Form Builder additions as settings, not new tables:
 *   forms.settings        success action (message | page | WhatsApp), WhatsApp
 *                         greeting, "then offer a page" link
 *   form_fields.settings  step number, show options as chips, the field's
 *                         WhatsApp sentence, what a hidden field fills itself with
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table): void {
            $table->json('settings')->nullable()->after('is_active');
        });
        Schema::table('form_fields', function (Blueprint $table): void {
            $table->json('settings')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', fn (Blueprint $table) => $table->dropColumn('settings'));
        Schema::table('forms', fn (Blueprint $table) => $table->dropColumn('settings'));
    }
};
