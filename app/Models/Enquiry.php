<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    /** source key => admin label */
    public const SOURCES = [
        'footer' => 'Footer Form',
        'contact' => 'Contact Page Form',
        'distributor' => 'Distributor Form',
        'dealer' => 'Dealer Form',
        'career' => 'Career Form',
    ];

    public const STATUSES = ['new', 'read', 'replied', 'closed'];

    protected $fillable = [
        'source',
        'name',
        'email',
        'phone',
        'message',
        'extra',
        'ip',
        'status',
        'admin_notes',
        'form_submission_id',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst($this->source);
    }

    /** Apply admin index filters: source, status, free-text search, date range. */
    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['source'] ?? null, fn ($q, $source) => $q->where('source', $source))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['q'] ?? null, function ($q, $term): void {
                $q->where(function ($w) use ($term): void {
                    $w->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('message', 'like', "%{$term}%");
                });
            })
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to));
    }
}
