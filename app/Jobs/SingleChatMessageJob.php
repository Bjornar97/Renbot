<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\BotService;
use Exception;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\ClientOptions;
use GhostZero\Tmi\Events\Irc\WelcomeEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        $client = BotService::bot();

        $client->on(WelcomeEvent::class, function () use ($client) {
            $client->say(config("services.twitch.channel"), $this->message);

            $client->getLoop()->addTimer(3, fn () => $client->close());
        });

        $client->connect();
    }
}
