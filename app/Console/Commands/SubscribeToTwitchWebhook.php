<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use romanzipp\Twitch\Enums\EventSubType;
use romanzipp\Twitch\Enums\GrantType;
use romanzipp\Twitch\Twitch;

class SubscribeToTwitchWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch:subscribe-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to twitch webhooks';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $twitch = new Twitch();
        $twitch->withClientId(config('services.twitch.client_id'))
            ->withClientSecret(config('services.twitch.client_secret'));

        $result = $twitch->getOAuthToken(null, GrantType::CLIENT_CREDENTIALS, [
            "user:read:chat",
            "user:bot",
        ]);

        if (! $result->success()) {
            dd($result->data());
        }

        $twitch->withToken($result->data()->access_token);

        $response = $twitch->subscribeEventSub([], [
            'type' => "channel.chat.notification",
            'version' => "1",
            'condition' => [
                'broadcaster_user_id' => config('services.twitch.channel_id'),
                'user_id' => config('services.twitch.bot_id'),
            ],
            'transport' => [
                'method' => 'webhook',
                'callback' => config('services.twitch.webhook_callback_url'),
                'secret' => config('services.twitch.webhook_secret'),
            ],
        ]);

        // $response = $twitch->unsubscribeEventSub([
        //     'id' => '39ac19b5-abd5-4615-a083-0c091e56006b',
        // ]);

        dd($response->data());
    }
}
