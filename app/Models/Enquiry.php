<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
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

    /**
     * Every enquiry source, key => label: one per form that saves its
     * submissions as enquiries (labelled with the form's name), plus any
     * older source still on stored enquiries. Remembered for the request.
     *
     * @return array<string, string>
     */
    public static function sources(): array
    {
        return \Illuminate\Support\Facades\Cache::store('array')->rememberForever('enquiry.sources', function (): array {
            $forms = Form::query()->whereNotNull('enquiry_source')->orderBy('name')->pluck('name', 'enquiry_source')->all();
            $stored = static::query()->distinct()->pluck('source')->filter()
                ->mapWithKeys(fn (string $source) => [$source => \Illuminate\Support\Str::headline($source)])
                ->all();

            return $forms + $stored;
        });
    }

    public function sourceLabel(): string
    {
        return self::sources()[$this->source] ?? \Illuminate\Support\Str::headline((string) $this->source);
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
