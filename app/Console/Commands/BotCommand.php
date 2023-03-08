<?php

namespace App\Console\Commands;

use App\Models\Command as ModelsCommand;
use App\Models\User;
use App\Services\CommandService;
use App\Services\MessageService;
use App\Services\PunishService;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\ClientOptions;
use GhostZero\Tmi\Events\Event;
use GhostZero\Tmi\Events\Irc\WelcomeEvent;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use GhostZero\Tmi\Events\Twitch\NoticeEvent;
use GhostZero\Tmi\Events\Twitch\UserNoticeEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
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
            'options' => ['debug' => config("app.tmi_debug", false)],
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
            if ($message->self) return;

            $this->onMessage($message);
        });

        $this->client->on(WelcomeEvent::class, $this->afterStartup(...));

        $this->client->connect();

        return Command::SUCCESS;
    }

    private function handleExit()
    {
        if (Feature::active("announce-restart")) {
            $this->client->say($this->channel, "Restarting. Dont use any commands right now!");
        }

        Cache::set("bot-shutdown-time", now()->timestamp, now()->addHours(6));

        $this->client->getLoop()->addTimer(3, fn () => $this->client->close());
    }

    private function afterStartup()
    {
        $lastShutdown = Cache::get("bot-shutdown-time");

        if (!$lastShutdown) {
            return;
        }

        Cache::delete("bot-shutdown-time");

        $lastShutdown = Carbon::createFromTimestamp($lastShutdown);

        $interval = CarbonInterval::seconds($lastShutdown->diffInSeconds())->cascade();

        if (Feature::active("announce-restart")) {
            $this->client->say($this->channel, "Im back after restart! I was gone for {$interval->forHumans()}");
        }
    }

    private function getAccessToken()
    {
        $renbot = User::where('username', "RenTheBot")->first();

        if (!$renbot) {
            throw new Exception("RenTheBot has not logged in, and its required for the bot to work.");
        }

        return $renbot->twitch_access_token;
    }

    public function onMessage(MessageEvent $message)
    {
        try {
            $this->analyzeForPunishment($message);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }

        try {
            $response = CommandService::message($message, $this->client)->getResponse();
            $this->client->say($this->channel, $response);
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            return;
        }
    }

    private function analyzeForPunishment(MessageEvent $message)
    {
        Feature::when(
            "auto-caps-punishment",
            whenActive: fn () => $this->capsCheck($message),
        );
    }

    private function capsCheck(MessageEvent $message)
    {
        $caps = $this->getNumberOfCaps($message->message);
        $percentage = $this->getPercentageOfCaps($message->message);

        if ($caps > 4 && $percentage > 0.5) {
            $messageService = MessageService::message($message);
            $userId = $messageService->getSenderTwitchId();
            $displayName = $messageService->getSenderDisplayName();

            $command = ModelsCommand::where('command', "caps")->first();

            $response = PunishService::user($userId, $displayName)
                ->command($command)
                ->bot($this->client)
                ->punish();

            $this->client->say($this->channel, $response);
        }
    }

    private function getNumberOfCaps(string $message): int
    {
        $message = str_replace(" ", "", $message);

        return strlen(preg_replace("/[^A-Z]/", "", $message));
    }

    private function getPercentageOfCaps(string $message): float
    {
        $message = str_replace(" ", "", $message);

        $total = strlen($message);
        $caps = strlen(preg_replace("/[^A-Z]/", "", $message));

        return $caps / $total;
    }
}
