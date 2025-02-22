<?php

namespace App\Jobs\Webhooks;

use App\Models\Channel;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use Spatie\WebhookClient\Models\WebhookCall;

class TwitchWebhookJob extends ProcessWebhookJob
{
    public function __construct(
        public WebhookCall $webhookCall
    ) {
        $this->onConnection('sync');

        parent::__construct($webhookCall);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $eventType = $this->webhookCall->payload['subscription']['type'];

        if (! isset($this->webhookCall->payload['event'])) {
            Log::notice('No event supplied with webhook!');

            return;
        }

        match ($eventType) {
            'channel.chat.message' => $this->message($this->webhookCall->payload['event']),
            'stream.online' => $this->streamOnline($this->webhookCall->payload['event']),
            'stream.offline' => $this->streamOffline($this->webhookCall->payload['event']),
            default => Log::info('Other twitch webhook received', ['event' => $this->webhookCall->payload]),
        };
    }

    /**
     * Add the message to the database.
     *
     * @param  array<string|null|array<string,string|null|array<int,string|array<string,null>>>>  $event
     */
    private function message(array $event): void
    {
        Log::info('Twitch message webhook received', ['message' => $event['message']['text']]);

        DB::transaction(function () use ($event) {
            Message::query()->updateOrCreate(
                [
                    'twitch_user_id' => $event['chatter_user_id'],
                    'message_id' => $event['message_id'],
                ],
                [
                    'message' => $event['message']['text'],
                    'webhook_recieved_at' => now(),
                    'fragments' => json_encode($event['message']['fragments'] ?? null),
                    'badges' => json_encode($event['badges'] ?? null),
                    'reply_to_message_id' => $event['reply']['parent_message_id'] ?? null,
                    'username' => $event['chatter_user_login'],
                    'display_name' => $event['chatter_user_name'],
                    'user_color' => $event['color'] ?? null,
                ]
            );
        });
    }

    /**
     * Log the stream online event.
     *
     * @param  array<string, string|null>  $event
     */
    private function streamOnline(array $event): void
    {
        Log::info('Twitch stream online webhook received', ['event' => $event]);

        DB::transaction(function () use ($event) {
            Channel::query()->updateOrCreate(
                [
                    'twitch_channel_id' => $event['broadcaster_user_id'],
                ],
                [
                    'live_at' => Carbon::parse($event['started_at']),
                    'username' => $event['broadcaster_user_name'],
                ]
            );
        });
    }

    /**
     * Log the stream offline event.
     *
     * @param  array<string, string|null>  $event
     */
    private function streamOffline(array $event): void
    {
        Log::info('Twitch stream offline webhook received', ['event' => $event]);

        DB::transaction(function () use ($event) {
            Channel::query()->updateOrCreate(
                [
                    'twitch_channel_id' => $event['broadcaster_user_id'],
                ],
                [
                    'offline_at' => now(),
                    'username' => $event['broadcaster_user_name'],
                ]
            );
        });
    }
}
