<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;

class MessageService
{
    public function __construct(protected Message $message) {}

    public static function message(Message $message): self
    {
        return new self($message);
    }

    public function getModerator(): ?User
    {
        return User::where('twitch_id', $this->message->twitch_user_id)->first();
    }

    public function getMessageId(): string
    {
        return $this->message->message_id;
    }

    public function getTarget(): ?string
    {
        $message = trim($this->message->message);

        $words = explode(' ', $message);

        $target = $words[1] ?? null;

        if (! $target || ! str_starts_with($target, '@')) {
            return null;
        }

        $target = str_replace('@', '', $target);

        return $target;
    }

    public function getSenderDisplayName(): string
    {
        return $this->message->display_name;
    }

    public function getSenderTwitchId(): int
    {
        return (int) $this->message->twitch_user_id;
    }

    public function hasBadge(string $badge): bool
    {
        $badges = collect($this->message->badges);

        return $badges->where('set_id', $badge)->isNotEmpty();
    }

    public function isModerator(): bool
    {
        return $this->hasBadge('moderator') || $this->hasBadge('broadcaster');
    }

    public function isVIP(): bool
    {
        return $this->hasBadge('vip');
    }

    public function isSubscriber(): bool
    {
        return $this->hasBadge('subscriber');
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
                $start = (int) $emotePosition[0];
                $end = (int) $emotePosition[1];

                $length = $end - $start + 1;

                $string = substr_replace($string, str_repeat(' ', $length), $start, $length);
            }
        }

        // Remove double spaces
        $string = preg_replace("/\s\s+/", ' ', $string);

        return $string;
    }
}
