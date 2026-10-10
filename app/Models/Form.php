<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    /** What happens after a visitor sends the form. */
    public const SUCCESS_ACTIONS = [
        'message' => 'Show the success message',
        'page' => 'Go to a page',
        'whatsapp' => 'Open WhatsApp with a message built from the answers',
    ];

    protected $fillable = [
        'name',
        'slug',
        'heading',
        'button_label',
        'success_message',
        'notify_emails',
        'enquiry_source',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'notify_emails' => 'array',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest('id');
    }

    /**
     * settings: success_action (SUCCESS_ACTIONS), success_page (a link),
     * whatsapp_intro (the message's first line), then_label + then_page
     * (a page offered after WhatsApp opens).
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        $value = data_get($this->settings, $key);

        return $value === null || $value === '' ? $default : $value;
    }

    public function successAction(): string
    {
        return array_key_exists($action = (string) $this->setting('success_action', 'message'), self::SUCCESS_ACTIONS) ? $action : 'message';
    }

    /** The greeting, then each answered field's WhatsApp sentence ("Business type: {value}."). */
    public function whatsappMessage(array $answers): string
    {
        $parts = [trim((string) $this->setting('whatsapp_intro', ''))];

        foreach ($this->fields as $field) {
            $value = $answers[$field->name] ?? null;
            $value = trim(is_array($value) ? implode(', ', array_filter(array_map('strval', $value))) : (string) $value);
            if ($value !== '' && ($sentence = (string) $field->setting('whatsapp', '')) !== '') {
                $parts[] = str_replace('{value}', $value, $sentence);
            }
        }

        return implode(' ', array_filter($parts));
    }

    /** wa.me link to the business WhatsApp number with the message, or null without a number. */
    public function whatsappUrl(array $answers): ?string
    {
        $number = business_whatsapp()['intl'] ?? null;

        return $number ? 'https://wa.me/'.$number.'?text='.rawurlencode($this->whatsappMessage($answers)) : null;
    }
}
