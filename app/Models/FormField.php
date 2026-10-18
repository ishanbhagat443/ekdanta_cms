<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    public const TYPES = ['text', 'email', 'phone', 'select', 'radio', 'checkbox', 'consent', 'date', 'textarea', 'file', 'hidden'];

    /** What a ticked consent box posts: the wording it showed ("short" on phones). */
    public const CONSENT_VALUES = ['1', 'wide', 'short'];

    /** What a hidden field fills itself with in the visitor's browser. */
    public const AUTOFILL = [
        'page_url' => 'Page address',
        'referrer' => 'Where the visitor came from',
        'utm_source' => 'UTM source',
        'utm_medium' => 'UTM medium',
        'utm_campaign' => 'UTM campaign',
        'utm_term' => 'UTM term',
        'utm_content' => 'UTM content',
    ];

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
        'settings',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'settings' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, ['select', 'radio', 'checkbox'], true);
    }

    /**
     * settings: step (1, 2, …), display ("chips" for radio/checkbox), whatsapp
     * (sentence with {value}), autofill (AUTOFILL); consent boxes: note, link_text,
     * link_url, and the phone wording short_label / short_note.
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        $value = data_get($this->settings, $key);

        return $value === null || $value === '' ? $default : $value;
    }

    public function step(): int
    {
        return max(1, (int) $this->setting('step', 1));
    }

    /** A consent box's words as the visitor saw them: label, note and link text. */
    public function consentText(bool $short = false): string
    {
        $label = $short ? $this->setting('short_label', $this->label) : $this->label;
        $note = $short ? $this->setting('short_note', $this->setting('note', '')) : $this->setting('note', '');

        return trim(implode(' ', array_filter([$label, $note, $this->setting('link_text')])));
    }
}
