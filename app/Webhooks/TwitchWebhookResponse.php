<?php

namespace App\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo;
use Symfony\Component\HttpFoundation\Response;

class TwitchWebhookResponse extends DefaultRespondsTo
{
    public function respondToValidWebhook(Request $request, WebhookConfig $webhookConfig): Response
    {
        if ($request->header('Twitch-Eventsub-Message-Type') === 'webhook_callback_verification') {
            Log::notice('Responding to webhook challenge');
            return response($request->input('challenge'), 200)
                ->header('Content-Type', 'text/plain');
        }

        return parent::respondToValidWebhook($request, $webhookConfig);
    }
}
