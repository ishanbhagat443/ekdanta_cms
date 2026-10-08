<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    /**
     * Suggested placements shown in the admin "Location" picker. Purely
     * descriptive — any menu can still be assigned to any builder slot
     * (NavMenuSection / FooterLinksSection pick by menu_id, not location).
     */
    public const LOCATIONS = [
        'header' => 'Header Menu',
        'footer' => 'Footer Menu',
        'mobile' => 'Mobile Menu',
        'quick-links' => 'Quick Links',
        'sidebar' => 'Sidebar Menu',
        'mega' => 'Mega Menu',
        'custom' => 'Custom Location',
    ];

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function locationLabel(): string
    {
        return self::LOCATIONS[$this->location] ?? '—';
    }
}
