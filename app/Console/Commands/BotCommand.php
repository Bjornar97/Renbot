<?php

namespace App\Console\Commands;

use App\Jobs\Analysis\AnalyzeCapsJob;
use App\Jobs\Analysis\AnalyzeEmotesJob;
use App\Jobs\AutoPostCheckJob;
use App\Models\Message;
use App\Services\BotService;
use App\Services\CommandService;
use App\Services\MessageService;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use GhostZero\Tmi\Client;
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

    private Carbon $lastFeatureFlush;



    public function __construct()
    {
        parent::__construct();

        $this->lastFeatureFlush = now();
    }

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
        $this->maybeFlushFeatures();

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

    private function maybeFlushFeatures()
    {
        if ($this->lastFeatureFlush->diffInSeconds(now()) > 10) {
            Log::info("Flushing features");
            Feature::flushCache();
            $this->lastFeatureFlush = now();
        }
    }

    public function onMessage(MessageEvent $message)
    {
        $this->maybeFlushFeatures();

        try {
            $messageService = MessageService::message($message);

            Message::query()->create([
                'twitch_user_id' => $messageService->getSenderTwitchId(),
                'message' => $message->message,
            ]);

            $this->checkAutoPost($message);

            if (!$messageService->isModerator()) {
                $this->analyzeForPunishment($message);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }

        try {
            $commandService = CommandService::message($message, $this->client);
            $response = $commandService->getResponse();

            $this->client->say($this->channel, $response);
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            return;
        }
    }

    private function checkAutoPost(MessageEvent $message)
    {
        AutoPostCheckJob::dispatch($message);
    }

    private function analyzeForPunishment(MessageEvent $message)
    {
        Feature::when(
            "auto-caps-punishment",
            whenActive: fn () => AnalyzeCapsJob::dispatch($message),
        );

        Feature::when(
            "auto-max-emotes-punishment",
            whenActive: fn () => AnalyzeEmotesJob::dispatch($message),
        );
    }
}
