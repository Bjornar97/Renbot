<?php

namespace App\Jobs;

use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use romanzipp\Twitch\Twitch;

class BanTwitchUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $twitchUserId, public ?string $reason, public ?User $moderator)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $moderator = $this->getModerator();

        $twitch = new Twitch();
        $twitch->setToken($moderator->twitch_access_token);

        $twitch->banUser([
            'broadcaster_id' => config("services.twitch.channel_id"),
            'moderator_id' => $moderator->twitch_id,
        ], [
            'data' => [
                'user_id' => $this->twitchUserId,
                'reason' => $this->reason,
            ]
        ]);
    }

    public function getModerator(): User
    {
        $moderator = $this->moderator;

        if (!$moderator) {
            $moderator = User::where('username', config("services.twitch.username"))->first();
            if (!$moderator) {
                throw new Exception("RenTheBot is not registered!");
            }
        }

        return $moderator;
    }
}
