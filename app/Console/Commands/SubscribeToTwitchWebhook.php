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
    protected $signature = 'twitch:subscribe-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to twitch webhooks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $twitch = new Twitch;
        $twitch->withClientId(config('services.twitch.client_id'))
            ->withClientSecret(config('services.twitch.client_secret'));

        $result = $twitch->getOAuthToken(null, GrantType::CLIENT_CREDENTIALS);

        if (! $result->success()) {
            $this->error('Failed to get twitch oauth token');

            return Command::FAILURE;
        }

        $twitch->withToken($result->data()->access_token);

        $channelSubsciptions = [
            EventSubType::STREAM_ONLINE => [
                'version' => '1',
                'condition' => [
                    'broadcaster_user_id' => config('services.twitch.channel_id'),
                ],
            ],
            EventSubType::STREAM_OFFLINE => [
                'version' => '1',
                'condition' => [
                    'broadcaster_user_id' => config('services.twitch.channel_id'),
                ],
            ],
            'channel.chat.message' => [
                'version' => '1',
                'condition' => [
                    'broadcaster_user_id' => config('services.twitch.channel_id'),
                    'user_id' => config('services.twitch.bot_id'),
                ],
            ],
            // TODO: Enable later when needed
            // 'channel.chat.clear' => [
            //     'version' => '1',
            //     'condition' => [
            //         'broadcaster_user_id' => config('services.twitch.channel_id'),
            //         'user_id' => config('services.twitch.bot_id'),
            //     ],
            // ],
            // 'channel.chat.clear_user_messages' => [
            //     'version' => '1',
            //     'condition' => [
            //         'broadcaster_user_id' => config('services.twitch.channel_id'),
            //         'user_id' => config('services.twitch.bot_id'),
            //     ],
            // ],
            // 'channel.chat.message_delete' => [
            //     'version' => '1',
            //     'condition' => [
            //         'broadcaster_user_id' => config('services.twitch.channel_id'),
            //         'user_id' => config('services.twitch.bot_id'),
            //     ],
            // ],
            // 'channel.chat.notification' => [
            //     'version' => '1',
            //     'condition' => [
            //         'broadcaster_user_id' => config('services.twitch.channel_id'),
            //         'user_id' => config('services.twitch.bot_id'),
            //     ],
            // ],
            // 'channel.warning.send' => [
            //     'version' => '1',
            //     'condition' => [
            //         'broadcaster_user_id' => config('services.twitch.channel_id'),
            //         'moderator_user_id' => config('services.twitch.bot_id'),
            //     ],
            // ],
            // 'channel.warning.acknowledge' => [
            //     'version' => '1',
            //     'condition' => [
            //         'broadcaster_user_id' => config('services.twitch.channel_id'),
            //         'moderator_user_id' => config('services.twitch.bot_id'),
            //     ],
            // ],
            // 'channel.moderate' => [
            //     'version' => '2',
            //     'condition' => [
            //         'broadcaster_user_id' => config('services.twitch.channel_id'),
            //         'moderator_user_id' => config('services.twitch.bot_id'),
            //     ],
            // ],
        ];

        $unsuccesfulSubscriptions = [];

        foreach ($channelSubsciptions as $type => $subscription) {
            $response = $twitch->subscribeEventSub([], [
                'type' => $type,
                'version' => $subscription['version'],
                'condition' => $subscription['condition'],
                'transport' => [
                    'method' => 'webhook',
                    'callback' => config('services.twitch.webhook_callback_url'),
                    'secret' => config('services.twitch.webhook_secret'),
                ],
            ]);

            if ($response->getStatus() === 409) {
                $this->info('Webhook already subscribed: '.$type);

                continue;
            }

            if (! $response->success()) {
                $unsuccesfulSubscriptions[] = ['type' => $type, 'subscription' => $subscription];
            }
        }

        if (! empty($unsuccesfulSubscriptions)) {
            $failedSubscriptions = array_map(fn ($sub) => $sub['type'], $unsuccesfulSubscriptions);
            $this->error('Failed to subscribe to the following webhooks: '.implode(', ', $failedSubscriptions));
        }

        $this->info('Successfully subscribed to twitch webhooks');

        return Command::SUCCESS;
    }
}
