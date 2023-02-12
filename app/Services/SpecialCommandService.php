<?php

namespace App\Services;

use App\Models\Command;
use App\Models\Punish;
use Exception;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Support\Facades\Log;

class SpecialCommandService
{
    public static $functions = [
        'resetPunishment' => [
            'action' => 'resetPunishment',
            'title' => "Reset punishment for user"
        ],
    ];

    public MessageEvent $message;
    public int|null $targetUserId = null;
    public string|null $targetUsername = null;

    public function __construct(public Command $command)
    {
    }

    public static function command(Command $command): self
    {
        return new self($command);
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
}
