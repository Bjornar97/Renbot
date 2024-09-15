<?php

namespace App\Jobs;

use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\Twitch\Twitch;

class SingleChatMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $message)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $messages = [$this->message];

        // Split string into chunks with max 500 characters. Splits by space, so it doesnt cut words.
        if (strlen($this->message) > 500) {
            $messages = explode("\n", wordwrap($this->message, 500));
        }

        $renbotUser = User::query()->where('username', 'RenTheBot')->first();

        $twitch = new Twitch();
        $twitch->setToken($renbotUser->twitch_access_token);

        foreach ($messages as $message) {
            $response = $twitch->post('chat/messages', [
                'broadcaster_id' => config("services.twitch.channel_id"),
                'sender_id' => $renbotUser->twitch_id,
                'message' => $message,
            ]);

            if ($response->getStatus() !== 200) {
                throw new Exception("Something went wrong sending message to chat. ", $response->getStatus());
            }
        }
    }
}
