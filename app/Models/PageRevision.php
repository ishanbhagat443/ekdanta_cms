<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One saved snapshot of a page and its sections. See PageRevisionService for
 * how snapshots are captured, pruned and restored.
 */
class PageRevision extends Model
{
    protected $fillable = [
        'page_id',
        'user_id',
        'label',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** "Deleted user" rather than a blank column once an account is removed. */
    public function authorName(): string
    {
        return $this->author?->name ?? 'Deleted user';
    }

    /** How many sections this snapshot holds — shown in the history list. */
    public function sectionCount(): int
    {
        return count($this->data['sections'] ?? []);
    }
}
