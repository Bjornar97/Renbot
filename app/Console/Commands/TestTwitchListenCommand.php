<?php

namespace App\Console\Commands;

use Exception;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\ClientOptions;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Console\Command;
use romanzipp\Twitch\Enums\GrantType;
use romanzipp\Twitch\Twitch;

class TestTwitchListenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing listening for messages in twitch';

    private Twitch $twitch;

    protected function setupTwitch()
    {
        $clientId = config("services.twitch.client_id");
        $secret = config("services.twitch.client_secret");
        $accessToken = config("services.twitch.access_token");

        $this->twitch = new Twitch;

        $this->twitch->setClientId($clientId);
        $this->twitch->setClientSecret($secret);

        $this->twitch->setToken($accessToken);

        dd([
            $this->getUserId("annabombanana"),
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setupTwitch();

        $oauthToken = config("services.twitch.oauth_token");

        $client = new Client(new ClientOptions([
            'options' => ['debug' => true],
            'connection' => [
                'secure' => true,
                'reconnect' => true,
                'rejoin' => true,
            ],
            'identity' => [
                'username' => 'RenTheBot',
                'password' => $oauthToken,
            ],
            'channels' => ['rendogtv'],
        ]));

        $client->on(MessageEvent::class, function (MessageEvent $e) use ($client) {
            if ($e->self) return;

            if (strtolower($e->message) === '!hello') {
                $this->timeout($this->getUserId("Alltidyoksa"), 10, "Testing testing, it is only 10 seconds");
            }
        });

        $client->connect();


        return Command::SUCCESS;
    }

    public function timeout(int $userId, int $duration, string $reason)
    {
        $this->info("Timing out user $userId");
        $this->twitch->banUser([
            'broadcaster_id' => $this->getUserId("rendogtv"),
            'moderator_id' => $this->getUserId("RenTheBot"),
        ], [
            'data' => [
                'user_id' => $userId,
                'duration' => $duration,
                'reason' => $reason,
            ]
        ]);
    }

    public function getUserId(string $username): int
    {
        $result = $this->twitch->getUsers(['login' => $username]);

        return (int) $result->data()[0]->id;
    }
}
