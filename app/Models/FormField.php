<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    public const TYPES = ['text', 'email', 'phone', 'select', 'radio', 'checkbox', 'date', 'textarea', 'file'];

    protected $fillable = [
        'form_id',
        'label',
        'name',
        'type',
        'placeholder',
        'options',
        'is_required',
        'validation_rules',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, ['select', 'radio', 'checkbox'], true);
    }
}
