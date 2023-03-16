<?php

namespace App\Jobs;

use App\Models\User;
use Exception;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\ClientOptions;
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
        $oauthToken = "oauth:{$this->getAccessToken()}";
        $channel = config("services.twitch.channel");
        $botUsername = config("services.twitch.username");

        $client = new Client(new ClientOptions([
            'options' => ['debug' => config("app.tmi_debug", false)],
            'connection' => [
                'secure' => true,
                'reconnect' => true,
                'rejoin' => true,
            ],
            'identity' => [
                'username' => $botUsername,
                'password' => $oauthToken,
            ],
            'channels' => [$channel],
        ]));

        $client->say($channel, $this->message);

        $client->getLoop()->addTimer(3, fn () => $client->close());
    }

    private function getAccessToken()
    {
        $renbot = User::where('username', "RenTheBot")->first();

        if (!$renbot) {
            throw new Exception("RenTheBot has not logged in, and its required for the bot to work.");
        }

        return $renbot->twitch_access_token;
    }
}
