<?php

namespace App\Services;

use App\Models\User;
use GhostZero\Tmi\Events\Twitch\MessageEvent;

class MessageService
{
    public function __construct(protected MessageEvent $message) {}

    public static function message(MessageEvent $message): self
    {
        return new self($message);
    }

    public function getModerator(): ?User
    {
        return User::where('twitch_id', $this->message->tags['user-id'])->first();
    }

    public function getMessageId(): string
    {
        return $this->message->tags['id'];
    }

    public function getTarget(): ?string
    {
        $message = trim($this->message->message);

        $words = explode(' ', $message);

        $target = $words[1] ?? null;

        if (! $target) {
            return null;
        }

        $target = str_replace('@', '', $target);

        return $target;
    }

    public function getSenderDisplayName(): string
    {
        return $this->message->tags['display-name'];
    }

    public function getSenderTwitchId(): string
    {
        return $this->message->tags['user-id'];
    }

    public function isModerator(): bool
    {
        return (bool) $this->message->tags['mod'];
    }

    public function isVIP(): bool
    {
        return (bool) $this->message->tags['vip'];
    }

    public function isThisBot(): bool
    {
        $botId = config('services.twitch.bot_id');

        return ((int) $botId) === ((int) $this->getSenderTwitchId());
    }

    public function getMessageWithoutEmotes(): string
    {
        $string = $this->message->message;

        $emotes = $this->message->tags['emotes'] ?? null;

        if (! $emotes) {
            return $this->message->message;
        }

        $emotes = explode('/', $emotes);

        foreach ($emotes as $emote) {
            $emote = explode(':', $emote);
            $emotePositions = $emote[1];

            $emotePositions = explode(',', $emotePositions);

            foreach ($emotePositions as $emotePosition) {
                $emotePosition = explode('-', $emotePosition);
                $start = $emotePosition[0];
                $end = $emotePosition[1];

                $length = (int) $end - (int) $start + 1;

                $string = substr_replace($string, str_repeat(' ', $length), $start, $length);
            }
        }

        // Remove double spaces
        $string = preg_replace("/\s\s+/", ' ', $string);

        return $string;
    }
}
