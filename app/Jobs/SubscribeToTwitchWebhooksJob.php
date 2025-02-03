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

class SubscribeToTwitchWebhooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $renbot = User::where('username', config('services.twitch.username'))->first();

        if (! $renbot) {
            Log::error('The bot is not registered as a user');

            return;
        }

        $twitch = new Twitch;
        $twitch->setToken($renbot->twitch_access_token);

        $twitch->subscribeEventSub([]);
    }
}
