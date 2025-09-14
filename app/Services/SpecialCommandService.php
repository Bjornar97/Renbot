<?php

namespace App\Services;

use App\Events\MakeNoiseEvent;
use App\Models\Command;
use App\Models\Message;
use App\Models\Punish;
use App\Models\Quote;
use Exception;

class SpecialCommandService
{
    /** @var array<string, array<string, string>> */
    public static array $functions = [
        'resetPunishment' => [
            'action' => 'resetPunishment',
            'title' => 'Reset punishment for user',
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

    public Message $message;

    public ?int $targetUserId = null;

    public ?string $targetUsername = null;

    public string $channel = 'rendogtv';

    public function __construct(public Command $command)
    {
        $this->channel = config('services.twitch.channel', 'rendogtv');
    }

    public static function command(Command $command): self
    {
        return new self($command);
    }

    public function message(Message $message): self
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

    public function run(): ?string
    {
        if (! $this->command->action) {
            throw new Exception('This special command does not have an action');
        }

        if (! isset(self::$functions[$this->command->action])) {
            throw new Exception("The action {$this->command->action} does not exist");
        }

        return $this->{self::$functions[$this->command->action]['action']}();
    }

    public function resetPunishment(): string
    {
        if (! $this->targetUserId) {
            throw new Exception("You need to specify a username to reset. Example: !{$this->command->command} @username");
        }

        Punish::where('twitch_user_id', $this->targetUserId)->delete();

        return "Successfully reset punishment for user {$this->targetUsername}";
    }

    public function makeSoundForRendog(): string
    {
        try {
            MakeNoiseEvent::dispatch();

            return $this->command->response;
        } catch (\Throwable $th) {
            throw new Exception('Something went wrong while making noise for Rendog');
        }
    }

    public function chatRandomQuote(): string
    {
        $quote = Quote::query()->inRandomOrder()->first();

        $chat = "\"{$quote->quote}\" - @{$quote->said_by}, {$quote->said_at->format('d/m/Y')}";

        return $chat;
    }
}
