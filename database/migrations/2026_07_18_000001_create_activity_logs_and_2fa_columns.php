<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DB-backed activity/login log — browsable from the admin panel.
        // Complements (does not replace) the file-based 'security' log
        // channel: this table is for admin visibility, the log file is
        // for ops/tail -f style monitoring and long-term forensic retention.
        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 50); // login|login_failed|lockout|logout|created|updated|deleted
            $table->string('subject_type', 100)->nullable(); // e.g. App\Models\Product
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description', 255)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });

        // Two-Factor Authentication readiness: columns exist, feature is
        // dormant until a future session wires up TOTP verification.
        Schema::table('users', function (Blueprint $table): void {
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });

        Schema::dropIfExists('activity_logs');
    }
};
