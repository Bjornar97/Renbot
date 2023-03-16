<?php

namespace App\Console\Commands;

use App\Jobs\Analysis\AnalyzeCapsJob;
use App\Models\Command as ModelsCommand;
use App\Models\User;
use App\Services\BotService;
use App\Services\CommandService;
use App\Services\MessageService;
use App\Services\PunishService;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\ClientOptions;
use GhostZero\Tmi\Events\Irc\WelcomeEvent;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->channel = config("services.twitch.channel");

        $this->client = BotService::bot();

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

    public function onMessage(MessageEvent $message)
    {
        try {
            $messageService = MessageService::message($message);

            if (!$messageService->isModerator()) {
                $this->analyzeForPunishment($message);
            }
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
            whenActive: fn () => AnalyzeCapsJob::dispatch($message),
        );
    }
}
