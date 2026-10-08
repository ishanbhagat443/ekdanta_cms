<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 191)->unique();
            $table->string('heading')->nullable();          // shown above the form
            $table->string('button_label', 60)->default('Submit');
            $table->string('success_message')->default('Thank you! We will get back to you shortly.');
            $table->json('notify_emails')->nullable();      // ["a@b.com", …]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('label', 150);
            $table->string('name', 100);                    // input name, auto from label
            $table->string('type', 20);                     // text|email|phone|select|radio|checkbox|date|textarea|file
            $table->string('placeholder', 150)->nullable();
            $table->json('options')->nullable();            // select/radio/checkbox choices
            $table->boolean('is_required')->default(false);
            $table->string('validation_rules')->nullable(); // extra pipe rules, e.g. "max:100"
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['form_id', 'name']);
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->json('data');                           // {field_name: value|[values]|file path}
            $table->string('ip', 45)->nullable();
            $table->string('status', 20)->default('new');   // new | read
            $table->timestamps();

            $table->index(['form_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
