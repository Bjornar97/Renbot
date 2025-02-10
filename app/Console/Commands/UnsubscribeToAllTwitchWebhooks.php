<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use romanzipp\Twitch\Enums\GrantType;
use romanzipp\Twitch\Twitch;

class UnsubscribeToAllTwitchWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch:unsubscribe-from-all-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $twitch->withToken($result->data()->access_token);

        $response = $twitch->getEventSubs();

        foreach ($response->data() as $webhook) {
            $twitch->unsubscribeEventSub([
                'id' => $webhook->id,
            ]);
        }
    }
}
