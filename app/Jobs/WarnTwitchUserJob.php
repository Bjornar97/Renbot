<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use romanzipp\Twitch\Result;
use romanzipp\Twitch\Twitch;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WarnTwitchUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $twitchUserId, public string $reason, public ?User $moderator)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $moderator = $this->moderator;

        if (!$moderator) {
            $moderator = User::where('username', config("services.twitch.username"))->first();
            if (!$moderator) {
                Log::error("The bot is not registered as a user");
                return;
            }
        }

        $error = false;

        try {
            $result = $this->warn($moderator);
        } catch (\Throwable $th) {
            $error = true;
            Log::error($th);
        }

        if ($error || $result?->getStatus() !== 200) {
            $moderator = User::where('username', config("services.twitch.username"))->first();
            $this->warn($moderator);
        }
    }

    private function warn(User $moderator): Result
    {
        $twitch = new Twitch();
        $twitch->setToken($moderator->twitch_access_token);

        $result = $twitch->post(
            'moderation/warnings',
            [
                'broadcaster_id' => config("services.twitch.channel_id"),
                'moderator_id' => $moderator->twitch_id,
            ],
            body: [
                'data' => [
                    'user_id' => $this->twitchUserId,
                    'reason' => $this->reason,
                ],
            ]
        );

        return $result;
    }
}
