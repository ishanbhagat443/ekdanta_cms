<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const EVENTS = [
        'login' => 'Login',
        'login_failed' => 'Failed Login',
        'lockout' => 'Lockout',
        'logout' => 'Logout',
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
    ];

    protected $fillable = [
        'user_id',
        'event',
        'subject_type',
        'subject_id',
        'description',
        'ip',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['event'] ?? null, fn ($q, $event) => $q->where('event', $event))
            ->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to));
    }

    public function eventLabel(): string
    {
        return self::EVENTS[$this->event] ?? ucfirst(str_replace('_', ' ', $this->event));
    }
}
