<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Meta WhatsApp webhook callback (URL: /whatsapp/webhook). The verify token is
 * admin-managed (Settings → WhatsApp). GET handles the subscription handshake;
 * POST receives delivery statuses / inbound messages (logged for now).
 */
class WhatsAppWebhookController extends Controller
{
    public function __construct(protected SettingsService $settings)
    {
    }

    /** Subscription verification handshake. */
    public function verify(Request $request): Response
    {
        $token = (string) $this->settings->get('whatsapp.webhook_verify_token', '');

        if (
            $request->query('hub_mode') === 'subscribe'
            && $token !== ''
            && hash_equals($token, (string) $request->query('hub_verify_token'))
        ) {
            return response((string) $request->query('hub_challenge'), 200);
        }

        return response('Forbidden', 403);
    }

    /** Inbound events (statuses, messages). Always 200 so Meta stops retrying. */
    public function receive(Request $request): Response
    {
        Log::info('WhatsApp webhook event', $request->all());

        return response('', 200);
    }
}
