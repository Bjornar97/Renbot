<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use romanzipp\Twitch\Twitch;

class TimeoutTwitchUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public int $twitchUserId, public int $seconds, public ?string $reason, public ?User $moderator)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $moderator = $this->moderator;

        if (!$moderator) {
            $moderator = User::where('username', config("services.twitch.username"))->first();
            if (!$moderator) {
                Log::error("The bot is not registered as a user");
                return;
            }
        }

        $twitch = new Twitch();
        $twitch->setToken($moderator->twitch_access_token);

        $result = $twitch->banUser([
            'broadcaster_id' => config("services.twitch.channel_id"),
            'moderator_id' => $moderator->twitch_id,
        ], [
            'data' => [
                'user_id' => $this->twitchUserId,
                'duration' => $this->seconds,
                'reason' => $this->reason,
            ]
        ]);
    }
}
