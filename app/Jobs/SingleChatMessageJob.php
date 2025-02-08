<?php

namespace App\Jobs;

use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use romanzipp\Twitch\Twitch;

class SingleChatMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $renbotUser;

    private Twitch $twitch;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $type, private string $message, private ?string $replyToId = null, private ?string $announcementColor = null)
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

        $this->renbotUser = User::query()->where('username', config('services.twitch.username'))->first();

        $this->twitch = new Twitch;
        $this->twitch->setToken($this->renbotUser->twitch_access_token);

        foreach ($messages as $message) {
            if ($this->type === 'chat') {
                $this->sendChatMessage($message);
            } elseif ($this->type === 'announcement') {
                $this->sendAnnouncement($message, $this->announcementColor);
            }
        }
    }

    private function sendChatMessage(string $message): void
    {
        $response = $this->twitch->post('chat/messages', [
            'broadcaster_id' => config('services.twitch.channel_id'),
            'sender_id' => $this->renbotUser->twitch_id,
            'message' => $message,
            'reply_parent_message_id' => $this->replyToId,
        ]);

        if ($response->getStatus() !== 200) {
            throw new Exception('Something went wrong sending message to chat. ', $response->getStatus());
        }
    }

    private function sendAnnouncement(string $message, string $color): void
    {
        $response = $this->twitch->post('chat/announcements', [
            'broadcaster_id' => config('services.twitch.channel_id'),
            'message' => $message,
            'moderator_id' => $this->renbotUser->twitch_id,
            'color' => $color,
        ]);

        if ($response->getStatus() !== 204) {
            Log::error((string) $response->getStatus());
            throw new Exception('Something went wrong sending announcement to chat. ', $response->getStatus());
        }
    }
}
