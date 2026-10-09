<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Collections (case studies, services, clients, reels, FAQs, team roles…):
 * one table, typed by App\Collections\*. The fields of each item live in
 * `data`, validated by its collection type, the same way section fields are.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_items', function (Blueprint $table): void {
            $table->id();
            $table->string('collection', 40);
            $table->string('slug', 120);
            $table->string('title', 200);
            $table->string('status', 20)->default('published'); // draft | published
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['collection', 'slug']);
            $table->index(['collection', 'status', 'sort_order']);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::findOrCreate('collections.manage', 'web');
        foreach (['Admin', 'Content Manager'] as $roleName) {
            Role::query()->where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_items');
        Permission::query()->where('name', 'collections.manage')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
