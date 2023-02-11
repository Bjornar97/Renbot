<?php

namespace App\Console\Commands;

use App\Services\CommandService;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\ClientOptions;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Console\Command;
use Throwable;

class BotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the bot';

    private Client $client;
    private string $channel;
    private string $botUsername;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $oauthToken = config("services.twitch.oauth_token");
        $this->channel = config("services.twitch.channel");
        $this->botUsername = config("services.twitch.username");

        $this->client = new Client(new ClientOptions([
            'options' => ['debug' => true],
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

        $this->client->on(MessageEvent::class, function (MessageEvent $message) {
            $this->onMessage($message);
        });

        $this->client->connect();

        return Command::SUCCESS;
    }

    public function onMessage(MessageEvent $message)
    {
        if ($message->self) return;

        try {
            $response = CommandService::message($message)->getResponse();

            $this->client->say($this->channel, $response);
        } catch (Throwable $th) {
            return;
        }
    }
}
