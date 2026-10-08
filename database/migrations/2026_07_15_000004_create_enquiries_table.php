<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Central enquiry inbox — every lead lands here regardless of which
        // form captured it. `source` keeps the streams separate.
        Schema::create('enquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('source', 30)->index(); // footer|contact|distributor|dealer|career
            $table->string('name', 191)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('message')->nullable();
            $table->json('extra')->nullable(); // form-specific fields beyond the core four
            $table->string('ip', 45)->nullable();
            $table->string('status', 20)->default('new'); // new|read|replied|closed
            $table->text('admin_notes')->nullable();
            $table->foreignId('form_submission_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['source', 'status']);
            $table->index('created_at');
        });

        // A form flagged with a source funnels its submissions into enquiries.
        Schema::table('forms', function (Blueprint $table): void {
            $table->string('enquiry_source', 30)->nullable()->after('notify_emails');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table): void {
            $table->dropColumn('enquiry_source');
        });
        Schema::dropIfExists('enquiries');
    }
};
