<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'heading',
        'button_label',
        'success_message',
        'notify_emails',
        'enquiry_source',
        'is_active',
    ];

    protected $casts = [
        'notify_emails' => 'array',
        'is_active' => 'boolean',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest('id');
    }
}
