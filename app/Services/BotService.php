<?php

namespace App\Services;

use App\Models\User;
use Exception;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\ClientOptions;

class BotService
{
    protected Client $client;

    protected string $channel;

    protected string $botUsername;

    public function __construct()
    {
        $oauthToken = "oauth:{$this->getAccessToken()}";
        $this->channel = config('services.twitch.channel');
        $this->botUsername = config('services.twitch.username');

        $this->client = new Client(new ClientOptions([
            'options' => ['debug' => config('app.tmi_debug', false)],
            'connection' => [
                'secure' => true,
                'reconnect' => true,
                'rejoin' => true,
            ],
            'identity' => [
                'username' => $this->botUsername,
                'password' => $oauthToken,
            ],
            'channels' => [$this->channel],
        ]));
    }

    private function getAccessToken()
    {
        $renbot = User::where('username', 'RenTheBot')->first();

        if (! $renbot) {
            throw new Exception('RenTheBot has not logged in, and its required for the bot to work.');
        }

        return $renbot->twitch_access_token;
    }

    public function getBot(): Client
    {
        return $this->client;
    }

    public static function bot(): Client
    {
        return app(self::class)->getBot();
    }
}
