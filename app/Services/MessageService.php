<?php

namespace App\Services;

use App\Models\User;
use GhostZero\Tmi\Events\Twitch\MessageEvent;

class MessageService
{
    public function __construct(protected MessageEvent $message)
    {
    }

    public static function message(MessageEvent $message): self
    {
        return new self($message);
    }

    public function getModerator(): User|null
    {
        return User::where('twitch_id', $this->message->tags['user-id'])->first();
    }

    public function getTarget(): string|null
    {
        $message = trim($this->message->message);

        $words = explode(" ", $message);

        $target = $words[1] ?? null;

        if (!$target) {
            return null;
        }

        $target = str_replace("@", "", $target);

        return $target;
    }

    public function getSenderDisplayName(): string
    {
        return $this->message->tags['display-name'];
    }

    public function getSenderUsername(): string
    {
        return $this->message->tags['username'];
    }

    public function getSenderTwitchId(): string
    {
        return $this->message->tags['user-id'];
    }

    public function isModerator(): bool
    {
        return (bool) $this->message->tags['mod'];
    }
}
