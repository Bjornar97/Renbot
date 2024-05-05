<?php

namespace App\Services;

use App\Enums\BotStatus;
use App\Events\MakeNoiseEvent;
use App\Models\Command;
use App\Models\Punish;
use App\Models\Quote;
use Exception;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

class SpecialCommandService
{
    public static $functions = [
        'resetPunishment' => [
            'action' => 'resetPunishment',
            'title' => "Reset punishment for user"
        ],
        'restartBot' => [
            'action' => "restartBot",
            'title' => "Restart the bot",
        ],
        'stopBot' => [
            'action' => "stopBot",
            'title' => "Stop the bot",
        ],
        'makeSoundForRendog' => [
            'action' => 'makeSoundForRendog',
            'title' => 'Make noise for Rendog',
        ],
        'chatRandomQuote' => [
            'action' => 'chatRandomQuote',
            'title' => 'Send a random quote to chat',
        ],
    ];

    public MessageEvent $message;
    public int|null $targetUserId = null;
    public string|null $targetUsername = null;

    public string $channel = "rendogtv";

    public function __construct(public Command $command, public Client $bot)
    {
        $this->channel = config("services.twitch.channel", "rendogtv");
    }

    public static function command(Command $command, Client $bot): self
    {
        return new self($command, $bot);
    }

    public function message(MessageEvent $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function target(int $targetUserId, string $targetUsername): self
    {
        $this->targetUserId = $targetUserId;
        $this->targetUsername = $targetUsername;
        return $this;
    }

    public function run()
    {
        if (!$this->command->action) {
            throw new Exception("This special command does not have an action");
        }

        if (!isset(self::$functions[$this->command->action])) {
            throw new Exception("The action {$this->command->action} does not exist");
        }

        return $this->{self::$functions[$this->command->action]['action']}();
    }

    public function resetPunishment(): void
    {
        if (!$this->targetUserId) {
            throw new Exception("You need to specify a username to reset. Example: !{$this->command->command} @username");
        }

        Punish::where('twitch_user_id', $this->targetUserId)->delete();
    }

    public function restartBot(): void
    {
        try {
            BotManagerService::restart();
        } catch (\Throwable $th) {
            throw new Exception("Failed to restart bot. Moderators, check the dashboard.");
        }
    }

    public function stopBot(): void
    {
        try {
            $this->bot->say($this->channel, "Stopping bot");
            BotManagerService::stop();
        } catch (\Throwable $th) {
            throw new Exception("Failed to stop bot. Moderators, check the dashboard.");
        }
    }

    public function makeSoundForRendog(): void
    {
        try {
            MakeNoiseEvent::dispatch();
        } catch (\Throwable $th) {
            throw new Exception("Something went wrong while making noise for Rendog");
        }
    }

    public function chatRandomQuote(): string
    {
        $quote = Quote::query()->inRandomOrder()->first();

        $chat = "\"{$quote->quote}\" - @{$quote->said_by}, {$quote->said_at->format('d/m/Y')}";

        return $chat;
    }
}
