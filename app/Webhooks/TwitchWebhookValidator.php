<?php

namespace App\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class TwitchWebhookValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature = $request->header('Twitch-Eventsub-Message-Signature');
        $messageId = $request->header('Twitch-Eventsub-Message-Id');
        $timestamp = $request->header('Twitch-Eventsub-Message-Timestamp');
        $body = $request->getContent();

        if (!$signature || !$messageId || !$timestamp) {
            Log::notice('Invalid signature, message id or timestamp');
            Log::debug('Signature: ' . $signature);
            Log::debug('Message ID: ' . $messageId);
            Log::debug('Timestamp: ' . $timestamp);
            return false;
        }

        $secret = config('services.twitch.webhook_secret');
        $message = $messageId . $timestamp . $body;
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $message, $secret);

        Log::notice('Expected signature: ' . $expectedSignature);
        Log::notice('Actual signature: ' . $signature);

        return hash_equals($expectedSignature, $signature);
    }
}
