<?php

namespace App\Services;

use App\Jobs\DeleteTwitchMessageJob;
use App\Models\Command;
use Carbon\Carbon;
use Exception;
use GhostZero\Tmi\Client;
use GhostZero\Tmi\Events\Twitch\MessageEvent;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use Throwable;

class CommandService
{
    public readonly Command $command;

    private MessageService $messageService;

    public string $channel = "rendogtv";

    public function __construct(public MessageEvent $message, private Client $bot)
    {
        $this->channel = config("services.twitch.channel", "rendogtv");

        $command = $this->getCommandFromMessage($message->message);

        if (!$command) {
            throw new Exception("No command found");
        }

        $this->messageService = MessageService::message($message);

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

        if ($this->isGlobalCooldown()) {
            if ($this->shouldCalloutCooldown()) {
                $message = "@{$this->messageService->getSenderDisplayName()} The !{$this->command->command} command is under global cooldown, please try again later.";
                return $message;
            }

            throw new Exception("Global cooldown");
        }

        if ($this->isUserCooldown()) {
            if ($this->shouldCalloutCooldown()) {
                $message = "@{$this->messageService->getSenderDisplayName()} The !{$this->command->command} command is under cooldown for you, please try again later.";
                return $message;
            }

            throw new Exception("User cooldown");
        }

        $this->setCooldownData();


        return match ($this->command->type) {
            "regular" => $this->regular(),
            "punishable" => $this->punishable(),
            "special" => $this->special(),
            default => "Invalid command type",
        };
    }

    private function isAuthorized()
    {
        $isModerator = $this->messageService->isModerator();
        $isVIP = $this->messageService->isVIP();

        if ($isModerator || $isVIP) {
            return true;
        }

        if ($this->command->everyone_can_use) {
            return true;
        }

        $isSubscriber = (bool) $this->message->tags['subscriber'];
        $isSubText = $isSubscriber ? 'Yes' : 'No';

        Log::debug("Subscriber: {$isSubText}");
        Log::debug("Can subscriber use: {$this->command->subscriber_can_use}");

        if ($isSubscriber && $this->command->subscriber_can_use) {
            return true;
        }

        // If user is broadcaster, user is authorized
        $badges = $this->message->tags['badges'] ?? "";
        $badges = explode(",", $badges);
        if (in_array("broadcaster/1", $badges)) {
            return true;
        }

        return false;
    }

    private function isGlobalCooldown()
    {
        $isGlobalCooldown = $this->command
            ->commandMetadata()
            ->where('type', 'data')
            ->where('key', 'globallyUsed')
            ->where(
                'updated_at',
                '>',
                now()->subSeconds($this->command->global_cooldown)
            )
            ->exists();

        return $isGlobalCooldown;
    }

    private function isUserCooldown()
    {
        $isUserCooldown = $this->command
            ->commandMetadata()
            ->where('type', 'data')
            ->where('key', "usedBy{$this->messageService->getSenderTwitchId()}")
            ->where(
                'updated_at',
                '>',
                now()->subSeconds($this->command->cooldown)
            )
            ->exists();

        return $isUserCooldown;
    }

    private function shouldCalloutCooldown()
    {
        $lastCalloutData = $this->command
            ->commandMetadata()
            ->where('type', 'data')
            ->where('key', "lastCooldownCallout")
            ->first();

        if ($lastCalloutData) {
            $lastCallout = Carbon::parse($lastCalloutData->value);

            if ($lastCallout->isAfter(now()->subMinute())) {
                return false;
            }
        }

        $this->command->commandMetadata()
            ->updateOrCreate(
                [
                    'type' => 'data',
                    'key' => "lastCooldownCallout",
                ],
                [
                    'value' => now(),
                ],
            );

        return true;
    }

    private function setCooldownData()
    {
        $this->command->commandMetadata()
            ->updateOrCreate(
                [
                    'type' => 'data',
                    'key' => 'globallyUsed',
                ],
                [
                    'value' => now(),
                ],
            );

        $this->command->commandMetadata()
            ->updateOrCreate(
                [
                    'type' => 'data',
                    'key' => "usedBy{$this->messageService->getSenderTwitchId()}",
                ],
                [
                    'value' => now(),
                ],
            );
    }

    private function generateBasicResponse(): string
    {
        $response = "";

        $target = $this->messageService->getTarget($this->message->message);

        if ($target) {
            $response .= "@{$target} ";
        } else if ($this->command->prepend_sender) {
            $response .= "@{$this->messageService->getSenderDisplayName()} ";
        }

        $response .= $this->command->response;

        return $response;
    }

    public function regular(): string
    {
        return $this->generateBasicResponse();
    }

    private function punishable(): string
    {
        $target = $this->messageService->getTarget($this->message->message);

        $moderator = $this->messageService->getModerator();

        if (!$target) {
            return $this->generateBasicResponse();
        }

        try {
            $twitchId = TwitchService::getTwitchId($target, $moderator);
        } catch (\Throwable $th) {
            return "@{$this->messageService->getSenderDisplayName()} {$th->getMessage()}";
        }

        $response = PunishService::user($twitchId, $target)
            ->command($this->command)
            ->moderator($moderator)
            ->bot($this->bot)
            ->basicResponse($this->generateBasicResponse())
            ->punish();

        return $response;
    }

    public function special(): string
    {
        $target = $this->messageService->getTarget($this->message->message);

        try {
            $command = SpecialCommandService::command($this->command, $this->bot)
                ->message($this->message)
                ->basicResponse($this->generateBasicResponse());

            if ($target) {
                $twitchId = TwitchService::getTwitchId($target);
                $command->target($twitchId, $target);
            }

            $response = $command->run();
        } catch (Throwable $th) {
            Log::error($th->getMessage());
            if (Feature::active("special-debug")) {
                return "@{$this->messageService->getSenderDisplayName()} " . $th->getMessage();
            }

            return "@{$this->messageService->getSenderDisplayName()} Something went wrong";
        }

        if ($response) {
            return $response;
        }

        return $this->generateBasicResponse();
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
}
