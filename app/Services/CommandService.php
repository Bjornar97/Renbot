<?php

namespace App\Services;

use App\Jobs\BanTwitchUserJob;
use App\Jobs\DeleteTwitchMessageJob;
use App\Jobs\TimeoutTwitchUserJob;
use App\Models\Command;
use App\Models\Punish;
use App\Models\User;
use Carbon\CarbonInterval;
use Exception;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use romanzipp\Twitch\Twitch;
use Throwable;

class CommandService
{
    public readonly Command $command;

    public string $channel = "rendogtv";

    public array $punishTable = [
        1 => 10,
        2 => 20,
        3 => 30,
        4 => 60,
        5 => 120,
        6 => 200,
        7 => 300,
        8 => 600,
        9 => 1200,
        10 => 30000, // insta-ban
    ];

    public function __construct(public MessageEvent $message, private Client $bot)
    {
        $command = $this->getCommandFromMessage($message->message);

        if (!$command) {
            throw new Exception("No command found");
        }

        $this->command = $command;
    }

    public static function message(MessageEvent $message, Client $bot): self
    {
        return new self($message, $bot);
    }

    public function getResponse(): string
    {
        if (!$this->isAuthorized()) {
            DeleteTwitchMessageJob::dispatch($this->message->tags['id']);

            throw new Exception("Unauthorized");
        }

        switch ($this->command->type) {
            case "regular":
                return $this->regular();

            case "punishable":
                return $this->punishable();

            case "special":
                return $this->special();

            default:
                return "Invalid command type";
        }
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

    private function generateBasicResponse(): string
    {
        $response = "";

        $target = $this->getTargetUsernameFromMessage($this->message->message);

        if ($target) {
            $response .= "@{$target} ";
        }

        $response .= $this->command->response;

        return $response;
    }

    private function getModerator(): User|null
    {
        return User::where('twitch_id', $this->message->tags['user-id'])->first();
    }

    public function regular(): string
    {
        return $this->generateBasicResponse();
    }

    private function punishable(): string
    {
        $target = $this->getTargetUsernameFromMessage($this->message->message);

        $moderator = $this->getModerator();

        if (!$target) {
            $this->bot->say($this->channel, "@{$this->getSenderDisplayName()} You need to specify which user to punish. Example: !{$this->command->command} @username");
            throw new Exception("Did not supply target for punishment");
        }

        $twitchId = $this->getTwitchId($target);

        if ($this->isJustPunished($twitchId)) {
            return "";
        }

        $seconds = $this->getPunishSeconds($twitchId);

        $response = $this->generateBasicResponse();

        if (Feature::active("bans") && $seconds >= 30000) {
            return $this->ban($twitchId, $response, $moderator);
        }

        if (Feature::active("timeouts")) {
            return $this->timeout($twitchId, $seconds, $response, $moderator);
        }

        return "";
    }

    private function ban(int $twitchId, string $response, User|null $moderator)
    {
        $response .= " [Ban]";

        $target = $this->getTargetUsernameFromMessage($this->message->message);

        $punish = $this->command->punishes()->create([
            'twitch_user_id' => $twitchId,
            'type' => "ban",
            'seconds' => -1,
        ]);

        activity()->on($punish)->by($moderator)->log("created");

        Feature::when(
            "punish-debug",
            whenActive: fn () => $this->bot->say($this->channel, "Would ban @{$target}"),
            whenInactive: fn () => BanTwitchUserJob::dispatch($twitchId, $this->command->punish_reason, $moderator),
        );

        return $response;
    }

    private function timeout(int $twitchId, int $seconds, string $response, User|null $moderator)
    {
        $timeString = CarbonInterval::seconds($seconds)->cascade()->forHumans();

        $response .= " [Timeout $timeString]";

        $target = $this->getTargetUsernameFromMessage($this->message->message);

        Feature::when(
            "punish-debug",
            whenActive: fn () => $this->bot->say($this->channel, "Would timeout @{$target} with {$seconds} seconds"),
            whenInactive: fn () => TimeoutTwitchUserJob::dispatch($twitchId, $seconds, $this->command->punish_reason, $moderator),
        );

        $punish = $this->command->punishes()->create([
            'twitch_user_id' => $twitchId,
            'type' => "timeout",
            'seconds' => $seconds,
        ]);

        activity()->on($punish)->by($moderator)->log("created");

        Log::info("Response: $response");

        return $response;
    }

    public function special(): string
    {
        $target = $this->getTargetUsernameFromMessage($this->message->message);

        try {
            $command = SpecialCommandService::command($this->command, $this->bot)
                ->message($this->message);

            if ($target) {
                $twitchId = $this->getTwitchId($target);
                $command->target($twitchId, $target);
            }

            $response = $command->run();
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            if (Feature::active("special-debug")) {
                return "@{$this->getSenderDisplayName()} " . $th->getMessage();
            }

            return "@{$this->getSenderDisplayName()} Something went wrong";
        }

        if ($response) {
            return $response;
        }

        return $this->generateBasicResponse();
    }

    public function getTwitchId(string $username): int
    {
        Log::info("Getting id");
        $moderator = $this->getModerator();

        Log::info("Got moderator: {$moderator->username}");

        $twitchId = Cache::remember("twitchId:{$username}", 8 * 60 * 60, function () use ($username, $moderator) {
            Log::info("Getting twitch id");
            $twitch = new Twitch;

            if ($moderator) {
                $twitch->setToken($moderator->twitch_access_token);
            }

            $result = $twitch->getUsers([
                'login' => $username,
            ]);

            if (!$result->success() || count($result->data()) === 0) {
                Log::info("Not successful!");
                $this->bot->say($this->channel, "The user $username does not seem to exist.");
                throw new Exception("Failed to get Id of user");
            }

            Log::info("Got id");
            Log::info($result->data());

            return $result->data()[0]->id;
        });

        return $twitchId;
    }

    private function isJustPunished(int $twitchId): bool
    {
        $exists = Punish::where('twitch_user_id', $twitchId)->where('created_at', '>', now()->subSeconds(10))->exists();

        Log::info("ID: $twitchId");
        Log::info("Exists: " . ($exists ? 'Yes' : "No"));

        if ($exists) {
            return true;
        }

        return false;
    }

    private function getPunishSeconds(int $twitchId): int
    {
        $punishedTimes = Punish::where('twitch_user_id', $twitchId)
            ->whereDate('created_at', '>', now()->subWeeks(2))
            ->count();

        $seconds = $this->punishTable[$this->command->severity];

        if ($punishedTimes > 0) {
            $last = Punish::where('twitch_user_id', $twitchId)->latest()->first();

            $seconds = ($last->seconds / 2) * ($punishedTimes + 1) * ($this->command->severity / 2 + 2);

            $seconds = max($seconds, 60);
        }

        $seconds = round($seconds, -1);

        $seconds = min($seconds, 1_209_600);

        return $seconds;
    }

    private function getCommandFromMessage(string $message): Command|null
    {
        $message = trim($message);

        $words = explode(" ", $message);

        $command = $words[0] ?? null;

        if (!str_starts_with($command, "!")) {
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

    private function getSenderDisplayName(): string
    {
        return $this->message->tags['display-name'];
    }
}
