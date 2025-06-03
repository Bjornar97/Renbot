<?php

use romanzipp\Twitch\Enums\EventSubType;

return [
    'webhooks' => [
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
        // The ones below are not yet used
        'channel.chat.clear' => [
            'version' => '1',
            'condition' => [
                'broadcaster_user_id' => config('services.twitch.channel_id'),
                'user_id' => config('services.twitch.bot_id'),
            ],
        ],
        'channel.chat.clear_user_messages' => [
            'version' => '1',
            'condition' => [
                'broadcaster_user_id' => config('services.twitch.channel_id'),
                'user_id' => config('services.twitch.bot_id'),
            ],
        ],
        'channel.chat.message_delete' => [
            'version' => '1',
            'condition' => [
                'broadcaster_user_id' => config('services.twitch.channel_id'),
                'user_id' => config('services.twitch.bot_id'),
            ],
        ],
        'channel.chat.notification' => [
            'version' => '1',
            'condition' => [
                'broadcaster_user_id' => config('services.twitch.channel_id'),
                'user_id' => config('services.twitch.bot_id'),
            ],
        ],
        'channel.warning.send' => [
            'version' => '1',
            'condition' => [
                'broadcaster_user_id' => config('services.twitch.channel_id'),
                'moderator_user_id' => config('services.twitch.bot_id'),
            ],
        ],
        'channel.warning.acknowledge' => [
            'version' => '1',
            'condition' => [
                'broadcaster_user_id' => config('services.twitch.channel_id'),
                'moderator_user_id' => config('services.twitch.bot_id'),
            ],
        ],
        'channel.moderate' => [
            'version' => '2',
            'condition' => [
                'broadcaster_user_id' => config('services.twitch.channel_id'),
                'moderator_user_id' => config('services.twitch.bot_id'),
            ],
        ],
    ],
];
