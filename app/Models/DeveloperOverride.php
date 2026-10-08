<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Developer Mode override for one Section: replacement HTML plus extra
 * CSS/JS. Originals are never touched — when an active override exists the
 * frontend renders it instead of the section's Blade component.
 */
class DeveloperOverride extends Model
{
    protected $fillable = ['section_id', 'html', 'css', 'js', 'is_active', 'updated_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DeveloperOverrideVersion::class)->latest('id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Snapshot the current state into the version history. */
    public function snapshot(?string $label = null, ?int $userId = null): DeveloperOverrideVersion
    {
        return $this->versions()->create([
            'html' => $this->html,
            'css' => $this->css,
            'js' => $this->js,
            'label' => $label,
            'created_by' => $userId,
            'created_at' => now(),
        ]);
    }
}
