<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use romanzipp\Twitch\Twitch;

class BanBotsFromBotList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const EXCLUDE_BOTS = [
        'Moobot',
        'StreamElements',
        'RenTheBot',
    ];

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
        if (Feature::inactive('auto-ban-bots')) {
            return;
        }

        $response = Http::get('https://api.twitchinsights.net/v1/bots/online');
        $response = $response->json();

        $bots = collect($response['bots'] ?? []);
        $bots = $bots->pluck(0);

        $renbotUser = User::query()->where('username', 'RenTheBot')->first();

        $twitch = new Twitch();
        $twitch->setToken($renbotUser->twitch_access_token);

        do {
            $nextCursor = null;

            // If this is not the first iteration, get the page cursor to the next set of results
            if (isset($result)) {
                $nextCursor = $result->next();
            }

            // Query the API with an optional cursor to the next results page
            $result = $twitch->getChatters([
                'broadcaster_id' => config("services.twitch.channel_id"),
                'moderator_id' => $renbotUser->twitch_id,
                'first' => 1000,
            ], $nextCursor);

            foreach ($result->data() as $chatter) {
                if (in_array($chatter->user_name, self::EXCLUDE_BOTS)) {
                    continue;
                }

                if ($bots->contains($chatter->user_login)) {
                    // Ban
                    BanTwitchUserJob::dispatch((int) $chatter->user_id, 'You are banned because you are an unwelcome bot. If this is a mistake, send an unban request, and we will review it.');
                }
            }

            // Continue until there are no results left
        } while ($result->hasMoreResults());
    }
}
