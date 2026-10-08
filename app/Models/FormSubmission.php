<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'data',
        'ip',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /** Human value for one field (arrays joined, file paths → URL text). */
    public function valueFor(string $fieldName): string
    {
        $value = $this->data[$fieldName] ?? '';

        return is_array($value) ? implode(', ', $value) : (string) $value;
    }
}
