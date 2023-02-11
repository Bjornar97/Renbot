<?php

namespace App\Services;

use App\Jobs\DeleteTwitchMessageJob;
use App\Models\Command;
use Exception;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Support\Facades\Log;

class CommandService
{
    public readonly Command $command;

    public function __construct(public MessageEvent $message)
    {
        $command = $this->getCommandFromMessage($message->message);

        if (!$command) {
            throw new Exception("No command found");
        }

        $this->command = $command;
    }

    public static function message(MessageEvent $message): self
    {
        return new self($message);
    }

    public function getResponse(): string
    {
        if (!$this->isAuthorized()) {
            Log::info("Dispatching deleting message");
            DeleteTwitchMessageJob::dispatch($this->message->tags['id']);
            throw new Exception("Unauthorized");
        }

        return $this->generateResponse();
    }

    private function isAuthorized()
    {
        $isModerator = (bool) $this->message->tags['mod'];

        if ($isModerator) {
            return true;
        }

        if ($this->command->everyone_can_use) {
            return true;
        }

        $isSubscriber = (bool) $this->message->tags['subscriber'];

        if ($isSubscriber && $this->command->subscriber_can_use) {
            return true;
        }

        return false;
    }

    private function generateResponse(): string
    {
        $response = "";

        $target = $this->getTargetUsernameFromMessage($this->message->message);

        if ($target) {
            $response .= "@{$target} ";
        }

        $response .= $this->command->response;

        return $response;
    }

    private function getCommandFromMessage(string $message): Command|null
    {
        $message = trim($message);

        $words = explode(" ", $message);

        $command = $words[0] ?? null;

        if (!str_contains($command, "!")) {
            return null;
        }

        $command = str_replace("!", "", $command);

        $command = Command::active()->where('command', $command)->first();

        return $command;
    }

    private function getTargetUsernameFromMessage(string $message): string | null
    {
        $message = trim($message);

        $words = explode(" ", $message);

        $target = $words[1] ?? null;

        if (!$target) {
            return null;
        }

        $target = str_replace("@", "", $target);

        return $target;
    }
}
