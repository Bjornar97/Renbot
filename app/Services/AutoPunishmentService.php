<?php

namespace App\Services;

use App\Models\Command;
use App\Models\User;
use GhostZero\Tmi\Client;

class AutoPunishmentService
{
    protected int $userId;
    protected string $username;

    protected Client $bot;

    public function __construct(protected string $message)
    {
    }

    public static function message(string $message): self
    {
        return new self($message);
    }

    public function user(int $userId, string $username): self
    {
        $this->userId = $userId;
        $this->username = $username;
        return $this;
    }

    public function bot(Client $bot): self
    {
        $this->bot = $bot;
        return $this;
    }

    public function analyze()
    {
    }

    protected function analyzeCaps()
    {
        $caps = preg_match_all("/[A-Z]/", $this->message);
        $total = strlen($this->message);
        $ratio = $caps / $total;

        if ($ratio > 0.5) {
            return true;
        }

        return false;
    }

    protected function punish(string $command)
    {
        $punishService = PunishService::user($this->userId, $this->username)
            ->command(Command::where("name", $command)->first())
            ->basicResponse()
            ->moderator(User::where("username", "renthebot")->first());

        if ($this->bot) {
            $punishService->punish();
        }
    }

    public function removeEmotes(string $emotes): self
    {
        $string = $this->message;

        if (!$emotes) {
            return $this->message;
        }

        $emotes = explode("/", $emotes);

        foreach ($emotes as $emote) {
            $emote = explode(":", $emote);
            $emoteId = $emote[0];
            $emotePositions = $emote[1];

            $emotePositions = explode(",", $emotePositions);

            foreach ($emotePositions as $emotePosition) {
                $emotePosition = explode("-", $emotePosition);
                $start = $emotePosition[0];
                $end = $emotePosition[1];

                $length = $end - $start + 1;

                $string = substr_replace($string, str_repeat(" ", $length), $start, $length);
            }
        }

        // Remove double spaces
        $string = preg_replace("/\s\s+/", " ", $string);

        $this->message = $string;

        return $this;
    }
}
