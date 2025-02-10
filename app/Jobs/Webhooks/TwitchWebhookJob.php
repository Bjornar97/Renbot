<?php

namespace App\Jobs\Webhooks;

use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class TwitchWebhookJob extends ProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Twitch webhook received');

        DB::transaction(function () {
            Message::updateOrCreate(
                [
                    'twitch_user_id' => $this->webhookCall->payload['event']['chatter_user_id'],
                    'message_id' => $this->webhookCall->payload['event']['message_id'],
                ],
                [
                    'message' => $this->webhookCall->payload['event']['message']['text'],
                    'webhook_recieved_at' => now(),
                ]
            );
        });
    }
}
