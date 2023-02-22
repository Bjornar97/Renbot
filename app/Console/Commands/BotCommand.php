<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CommandService;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\ClientOptions;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
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
        $oauthToken = "oauth:{$this->getAccessToken()}";
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



        // If OS asks to stop, say that bot is restarting, then close the connection
        $this->trap(SIGTERM, $this->handleExit(...));
        $this->trap(SIGHUP, $this->handleExit(...));
        $this->trap(SIGINT, $this->handleExit(...));

        $this->client->on(MessageEvent::class, function (MessageEvent $message) {
            $this->onMessage($message);
        });

        $this->client->connect();

        return Command::SUCCESS;
    }

    private function handleExit()
    {
        $this->client->say($this->channel, "This is Renbot 2.0 testing restarting.");

        Cache::set("bot-shutdown-time", now()->timestamp);

        sleep(2);

        $this->client->close();
    }

    private function getAccessToken()
    {
        $renbot = User::where('username', "RenTheBot")->first();

        return $renbot->twitch_access_token;
    }

    public function onMessage(MessageEvent $message)
    {
        if ($message->self) return;

        try {
            $lastShutdown = Cache::get("bot-shutdown-time");

            if ($lastShutdown) {
                $lastShutdown = Carbon::createFromTimestamp($lastShutdown);

                if ($lastShutdown->isAfter(now()->subHour())) {
                    $interval = CarbonInterval::seconds($lastShutdown->diffInSeconds())->cascade();

                    $this->client->say($this->channel, "Im back after test restart! After {$interval->forHumans()}");
                }

                Cache::delete("bot-shutdown-time");
            }

            $response = CommandService::message($message, $this->client)->getResponse();

            $this->client->say($this->channel, $response);
        } catch (Throwable $th) {
            return;
        }
    }
}
