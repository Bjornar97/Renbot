<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use romanzipp\Twitch\Twitch;

class DeleteTwitchMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public string $messageId)
    {
        Log::info("Created delete message job $messageId");
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Deleting message {$this->messageId}");
        $renbot = User::where('username', config("services.twitch.username"))->first();

        if (!$renbot) {
            Log::error("The bot is not registered as a user");
            return;
        }

        $twitch = new Twitch();
        $twitch->setToken($renbot->twitch_access_token);

        $result = $twitch->deleteChatMessages([
            'broadcaster_id' => config("services.twitch.channel_id"),
            'moderator_id' => config("services.twitch.bot_id"),
            'message_id' => $this->messageId,
        ]);

        Log::info("Status: ");
        Log::info($result->getStatus());
    }
}
