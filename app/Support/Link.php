<?php

declare(strict_types=1);

namespace App\Support;

/**
 * A link field: one text value that can be
 *   /page or /page#anchor     a page on this site
 *   #anchor                   a section on the same page
 *   https://…                 any web address
 *   tel:+91 90994 17987       a phone call
 *   mailto:hello@…            an email
 *   wa:Hi, I want to …        WhatsApp to the business number, message pre-filled
 *   wa:+91 63544 33194|Hi …   WhatsApp to another number
 */
final class Link
{
    public const HINT = 'A page (/contact), a section (#c-brief), a web address (https://…), tel:+91…, mailto:… or wa:your message (WhatsApp, message pre-filled).';

    public static function rules(bool $required = false): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value) && $value !== '' && ! self::valid($value)) {
                $fail('Use a page (/contact), a section (#c-brief), https://…, tel:…, mailto:… or wa:message.');
            }
        }];
    }

    public static function valid(string $value): bool
    {
        return preg_match('~^(/[^\s<>"]*|#[A-Za-z][\w-]*|https?://[^\s<>"]+|tel:\+?[0-9 ()-]{3,30}|mailto:[^\s<>"@]+@[^\s<>"@]+|wa:.{0,400})$~su', $value) === 1;
    }

    public static function href(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '' || ! self::valid($value)) {
            return '#';
        }

        if (str_starts_with($value, 'tel:')) {
            return 'tel:'.preg_replace('/[^0-9+]/', '', substr($value, 4));
        }

        if (! str_starts_with($value, 'wa:')) {
            return $value;
        }

        // wa:+91 63544 33194|message  or  wa:message (the business WhatsApp number)
        $message = substr($value, 3);
        $number = null;
        if (preg_match('/^(\+?[0-9 ()-]{6,20})\|(.*)$/s', $message, $m)) {
            [$number, $message] = [preg_replace('/\D/', '', $m[1]), $m[2]];
        }
        $number ??= business_whatsapp()['intl'] ?? '';

        return 'https://wa.me/'.$number.($message !== '' ? '?text='.rawurlencode($message) : '');
    }

    /** True when the link leaves the site (opens in a new tab). */
    public static function external(?string $value): bool
    {
        return (bool) preg_match('~^(https?://|wa:)~', (string) $value) && ! str_starts_with((string) $value, url('/'));
    }
}
