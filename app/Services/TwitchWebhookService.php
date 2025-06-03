<?php

namespace App\Services;

use Exception;
use romanzipp\Twitch\Enums\GrantType;
use romanzipp\Twitch\Twitch;

class TwitchWebhookService
{
    public function __construct(private Twitch $twitch = new Twitch)
    {
        $this->twitch->withClientId(config('services.twitch.client_id'))
            ->withClientSecret(config('services.twitch.client_secret'));
    }

    public static function connect(): self
    {
        $instance = new self;

        return $instance;
    }

    /**
     * Get all subscribed webhooks.
     *
     * @return array<string, mixed>
     */
    public function getSubscribedWebhooks(): array
    {
        $result = $this->twitch->getOAuthToken(null, GrantType::CLIENT_CREDENTIALS);

        $this->twitch->withToken($result->data()->access_token);

        $response = $this->twitch->getEventSubs();

        return $response->data();
    }

    public function isSubscribedToWebhook(string $name): bool
    {
        $webhook = config('twitch-webhooks.webhooks')[$name] ?? null;

        if (! $webhook) {
            throw new Exception("Webhook configuration for {$name} not found", 404);
        }

        $response = $this->twitch->getEventSubs([
            'type' => $name,
        ]);

        foreach ($response->data() as $webhookData) {
            if ($webhookData->type === $name && str_starts_with($webhookData->transport->callback, config('app.url'))) {
                return true;
            }
        }

        return false;
    }

    public function subscribeToWebhook(string $name): void
    {
        $webhook = config('twitch-webhooks.webhooks')[$name] ?? null;

        if (! $webhook) {
            throw new Exception("Webhook configuration for {$name} not found", 404);
        }

        $response = $this->twitch->subscribeEventSub([], [
            'type' => $name,
            'version' => $webhook['version'],
            'condition' => $webhook['condition'],
            'transport' => [
                'method' => 'webhook',
                'callback' => config('services.twitch.webhook_callback_url'),
                'secret' => config('services.twitch.webhook_secret'),
            ],
        ]);

        if ($response->getStatus() === 409) {
            throw new Exception("Webhook subscription for {$name} already exists", 409);
        }

        if (! $response->success()) {
            throw new Exception("Failed to subscribe to Twitch webhook: {$name}", 500);
        }
    }

    public function unsubscribeFromWebhook(string $name): void
    {
        $webhook = config('twitch-webhooks.webhooks')[$name] ?? null;

        if (! $webhook) {
            throw new Exception("Webhook configuration for {$name} not found", 404);
        }

        $response = $this->twitch->getEventSubs([
            'type' => $name,
        ]);

        foreach ($response->data() as $webhookData) {
            if ($webhookData->type === $name && str_starts_with($webhookData->transport->callback, config('app.url'))) {
                $this->twitch->unsubscribeEventSub([
                    'id' => $webhookData->id,
                ]);

                return;
            }
        }

        throw new Exception("Webhook subscription for {$name} not found", 404);
    }

    public function unsubscribeFromAllWebhooks(): void
    {
        $response = $this->twitch->getEventSubs();

        foreach ($response->data() as $webhook) {
            if (! str_starts_with($webhook->transport->callback, config('app.url'))) {
                continue;
            }

            $this->twitch->unsubscribeEventSub([
                'id' => $webhook->id,
            ]);
        }
    }
}
