<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Enquiry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends inquiry notifications through the Meta WhatsApp Cloud API.
 *
 * Every credential comes from Admin → Settings → WhatsApp (config/settings.php
 * "whatsapp" group) — nothing is hardcoded. The service is deliberately
 * fail-safe: a missing/disabled config or an API error is logged and swallowed
 * so the customer-facing inquiry flow always succeeds (the lead is already
 * stored in the CMS by the caller).
 */
class WhatsAppService
{
    public function __construct(protected SettingsService $settings)
    {
    }

    private function cfg(string $key, ?string $default = null): ?string
    {
        $value = $this->settings->get("whatsapp.{$key}", $default);

        return is_string($value) ? trim($value) : ($value === null ? $default : (string) $value);
    }

    public function enabled(): bool
    {
        return (bool) $this->settings->get('whatsapp.enabled', false);
    }

    /** Digits-only recipient MSISDN; falls back to the business WhatsApp number (Settings → Business). */
    public function recipient(): ?string
    {
        $raw = $this->cfg('recipient_number') ?: (business_whatsapp()['intl'] ?? '');
        $digits = preg_replace('/\D+/', '', (string) $raw);

        return $digits !== '' ? $digits : null;
    }

    public function isConfigured(): bool
    {
        return $this->enabled()
            && $this->cfg('phone_number_id')
            && $this->cfg('access_token')
            && $this->recipient();
    }

    /**
     * Push an inquiry to WhatsApp. Returns true only when the API accepted it.
     * Never throws — safe to call inline in a request lifecycle.
     */
    public function sendInquiry(Enquiry $enquiry): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $version = $this->cfg('api_version') ?: 'v21.0';
            $endpoint = "https://graph.facebook.com/{$version}/{$this->cfg('phone_number_id')}/messages";

            $response = Http::withToken($this->cfg('access_token'))
                ->acceptJson()
                ->timeout(15)
                ->post($endpoint, $this->payload($enquiry));

            if ($response->successful()) {
                return true;
            }

            Log::warning('WhatsApp inquiry send failed.', [
                'enquiry_id' => $enquiry->id,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('WhatsApp inquiry send threw.', [
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Template message when a template name is configured (delivers anytime);
     * otherwise a plain-text message (delivers only inside an active session).
     */
    private function payload(Enquiry $enquiry): array
    {
        $to = $this->recipient();
        $summary = $this->summary($enquiry);

        if ($template = $this->cfg('template_name')) {
            return [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => ['code' => $this->cfg('template_language') ?: 'en_US'],
                    'components' => [[
                        'type' => 'body',
                        'parameters' => [['type' => 'text', 'text' => $summary]],
                    ]],
                ],
            ];
        }

        return [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => $summary],
        ];
    }

    /* ── Custom (bring-your-own) WhatsApp gateway ────────────────────── */

    public function customApiUrl(): ?string
    {
        $url = trim((string) $this->settings->get('whatsapp_custom.api_url', ''));

        return $url !== '' ? $url : null;
    }

    /** Send through the admin-configured custom endpoint. Never throws. */
    public function sendViaCustomApi(string $message, array $context = []): bool
    {
        $url = $this->customApiUrl();
        if ($url === null) {
            return false;
        }

        try {
            $method = strtoupper((string) ($this->settings->get('whatsapp_custom.method') ?: 'POST'));
            $timeout = (int) ($this->settings->get('whatsapp_custom.timeout') ?: 15);
            $headers = json_decode((string) $this->settings->get('whatsapp_custom.headers', ''), true) ?: [];

            $replace = fn (string $tpl): string => str_replace(
                ['{{message}}', '{{phone}}'],
                [$message, $context['phone'] ?? ''],
                $tpl
            );

            $bodyTemplate = (string) $this->settings->get('whatsapp_custom.body_template', '');
            $body = $bodyTemplate !== ''
                ? (json_decode($replace($bodyTemplate), true) ?? ['message' => $message])
                : ['message' => $message] + $context;

            $pending = Http::withHeaders($headers)->timeout(max(3, min(60, $timeout)));
            $response = $method === 'GET'
                ? $pending->get($url, $body)
                : $pending->post($url, $body);

            $needle = trim((string) $this->settings->get('whatsapp_custom.success_contains', ''));
            $ok = $response->successful() && ($needle === '' || str_contains($response->body(), $needle));

            if (! $ok) {
                Log::warning('Custom WhatsApp API send failed.', ['status' => $response->status(), 'body' => mb_substr($response->body(), 0, 500)]);
            }

            return $ok;
        } catch (Throwable $e) {
            Log::error('Custom WhatsApp API threw.', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /** Admin "Send Test" button — routes through the active channel. */
    public function sendTest(): array
    {
        $message = '✅ Test message from '.setting('business.company_name', config('app.name')).' CMS ('.now()->format('d M Y H:i').').';

        if ($this->customApiUrl() !== null) {
            $ok = $this->sendViaCustomApi($message, ['phone' => $this->recipient() ?? '']);

            return [$ok, $ok ? 'Test sent through the Custom API.' : 'Custom API test failed — check the logs.'];
        }

        if (! $this->isConfigured()) {
            return [false, 'WhatsApp is not configured: enable API sending and fill Phone Number ID, Access Token and Recipient.'];
        }

        try {
            $version = $this->cfg('api_version') ?: 'v21.0';
            $response = Http::withToken($this->cfg('access_token'))
                ->acceptJson()->timeout(15)
                ->post("https://graph.facebook.com/{$version}/{$this->cfg('phone_number_id')}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $this->recipient(),
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $message],
                ]);

            return $response->successful()
                ? [true, 'Test message sent via the Meta Cloud API.']
                : [false, 'Cloud API rejected the test ('.$response->status().') — check credentials.'];
        } catch (Throwable $e) {
            return [false, 'Cloud API error: '.$e->getMessage()];
        }
    }

    /** Human-readable inquiry summary (also reused as the template body param). */
    public function summary(Enquiry $enquiry): string
    {
        $lines = [
            '🧵 New Inquiry — '.setting('business.company_name', config('app.name')),
            'Name: '.$enquiry->name,
            'Phone: '.$enquiry->phone,
        ];

        if ($enquiry->email) {
            $lines[] = 'Email: '.$enquiry->email;
        }

        foreach ((array) $enquiry->extra as $label => $value) {
            if ($value !== null && $value !== '') {
                $lines[] = $label.': '.$value;
            }
        }

        if ($enquiry->message) {
            $lines[] = 'Message: '.$enquiry->message;
        }

        return implode("\n", $lines);
    }
}
