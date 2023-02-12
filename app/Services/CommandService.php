<?php

namespace App\Services;

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
        10 => 0, // insta-ban
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
            Log::info("Dispatching deleting message");
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

        Log::info("Punishing $target");

        if (!$target) {
            $this->bot->say($this->channel, "You need to specify which user to punish. Example: !{$this->command->command} @username");
            throw new Exception("Did not supply target for punishment");
        }

        $twitchId = $this->getTwitchId($target);

        $seconds = $this->getPunishSeconds($twitchId);

        $response = $this->generateBasicResponse();

        if ($this->command->severity >= 10 || $seconds > 10000) {
            return $this->ban($twitchId, $response, $moderator);
        }

        $this->command->punishes()->create([
            'twitch_user_id' => $twitchId,
            'seconds' => $seconds,
        ]);

        return $this->timeout($twitchId, $seconds, $response, $moderator);
    }

    private function ban(int $twitchId, string $response, User|null $moderator)
    {
        $response .= " [Ban]";

        $target = $this->getTargetUsernameFromMessage($this->message->message);

        $this->bot->say($this->channel, "Would ban @{$target}");

        return $response;

        // TODO dispatch job to ban user
    }

    private function timeout(int $twitchId, int $seconds, string $response, User|null $moderator)
    {
        $timeString = CarbonInterval::seconds($seconds)->cascade()->forHumans();

        $response .= " [Timeout $timeString]";

        $target = $this->getTargetUsernameFromMessage($this->message->message);

        $this->bot->say($this->channel, "Would timeout @{$target} with {$seconds} seconds");

        return $response;

        TimeoutTwitchUserJob::dispatch($twitchId, $seconds, $this->command->punish_reason, $moderator);
    }

    public function special(): string
    {
        $target = $this->getTargetUsernameFromMessage($this->message->message);

        try {
            $command = SpecialCommandService::command($this->command)
                ->message($this->message);

            if ($target) {
                $twitchId = $this->getTwitchId($target);
                $command->target($twitchId, $target);
            }

            $response = $command->run();
        } catch (Throwable $th) {
            return $th->getMessage();
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

        return $seconds;
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
