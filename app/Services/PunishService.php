<?php

namespace App\Services;

use App\Jobs\BanTwitchUserJob;
use App\Jobs\SingleChatMessageJob;
use App\Jobs\TimeoutTwitchUserJob;
use App\Models\Command;
use App\Models\Punish;
use App\Models\User;
use Carbon\CarbonInterval;
use Exception;
use GhostZero\Tmi\Client;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;

class PunishService
{
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

    public string $channel = "rendogtv";

    private Command $command;

    private string|null $basicResponse = null;

    private User|null $moderator = null;

    private Client $bot;

    public function __construct(protected int $targetUserId, protected $targetUsername)
    {
        $this->channel = config("services.twitch.channel", "rendogtv");
    }

    public static function user(int $targetUserId, string $targetUsername): self
    {
        return new self($targetUserId, $targetUsername);
    }

    /**
     * The command to use to do punishment
     */
    public function command(Command $command): self
    {
        if ($command->type !== "punishable") {
            throw new Exception("Cannot punish with non-punishable command!");
        }

        $this->command = $command;
        return $this;
    }

    public function basicResponse(string $response): self
    {
        $this->basicResponse = $response;
        return $this;
    }

    public function moderator(User $moderator): self
    {
        $this->moderator = $moderator;
        return $this;
    }

    public function bot(Client $bot): self
    {
        $this->bot = $bot;
        return $this;
    }

    public function punish(): string
    {
        if ($this->isJustPunished($this->targetUserId)) {
            return "";
        }

        if (!$this->command) {
            throw new Exception("Command not set");
        }

        if (!$this->moderator) {
            $this->moderator = User::where("username", "renthebot")->first();

            if (!$this->moderator) {
                throw new Exception("RenTheBot not found");
            }
        }

        $seconds = $this->getPunishSeconds($this->targetUserId);

        $response = $this->basicResponse ?? "@{$this->targetUsername} {$this->command->response}";

        if (Feature::active("bans") && $seconds >= 30000) {
            return $this->ban($this->targetUserId, $response, $this->moderator);
        }

        if (Feature::active("timeouts")) {
            return $this->timeout($this->targetUserId, $seconds, $response, $this->moderator);
        }

        return $response;
    }

    private function ban(int $twitchId, string $response, User|null $moderator)
    {
        $response .= " [Ban]";

        $punish = $this->command->punishes()->create([
            'twitch_user_id' => $twitchId,
            'type' => "ban",
            'seconds' => -1,
        ]);

        activity()->on($punish)->by($moderator)->log("created");

        Feature::when(
            "punish-debug",
            whenActive: fn () => $this->say("Would ban @{$this->targetUsername}"),
            whenInactive: fn () => BanTwitchUserJob::dispatch($twitchId, $this->command->punish_reason, $moderator),
        );

        return $response;
    }

    private function timeout(int $twitchId, int $seconds, string $response, User|null $moderator)
    {
        $timeString = CarbonInterval::seconds($seconds)->cascade()->forHumans();

        $response .= " [Timeout $timeString]";

        Feature::when(
            "punish-debug",
            whenActive: fn () => $this->say("Would timeout @{$this->targetUsername} with {$seconds} seconds"),
            whenInactive: fn () => TimeoutTwitchUserJob::dispatch($twitchId, $seconds, $this->command->punish_reason, $moderator),
        );

        $punish = $this->command->punishes()->create([
            'twitch_user_id' => $twitchId,
            'type' => "timeout",
            'seconds' => $seconds,
        ]);

        activity()->on($punish)->by($moderator)->log("created");

        return $response;
    }

    private function isJustPunished(int $twitchId): bool
    {
        $exists = Punish::where('twitch_user_id', $twitchId)->where('created_at', '>', now()->subSeconds(10))->exists();

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

    private function say(string $message)
    {
        if (!isset($this->bot)) {
            SingleChatMessageJob::dispatch($message);
            return;
        }

        $this->bot?->say($this->channel, $message);
    }
}
