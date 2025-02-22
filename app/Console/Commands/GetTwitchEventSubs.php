<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use romanzipp\Twitch\Twitch;

class GetTwitchEventSubs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch:get-event-subs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $twitch = new Twitch;

        $twitch->withClientId(config('services.twitch.client_id'))
            ->withClientSecret(config('services.twitch.client_secret'));

        $twitch->getEventSubs();

        $response = $twitch->getEventSubs();
        $body = $response->getResponse()->getBody();
        $json = json_decode($body, true);

        $this->info(json_encode($json, JSON_PRETTY_PRINT));
    }
}
