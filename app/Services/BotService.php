<?php

namespace App\Services;

use App\Enums\BotStatus;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class BotService
{
    public function __construct()
    {
    }

    public static function getStatus(): BotStatus
    {
        $result = Process::run("supervisorctl status renbot");

        Log::debug("Did status fail: " . ($result->failed() ? 'Yes' : "No"));

        $output = $result->output();

        Log::debug($output);

        if (str_contains($output, "RUNNING")) {
            return BotStatus::RUNNING;
        }

        if (str_contains($output, "STOPPED")) {
            return BotStatus::STOPPED;
        }

        if (str_contains($output, "FAILED")) {
            return BotStatus::FAILED;
        }

        return BotStatus::UNKNOWN;
    }

    public static function restart(): void
    {
        $result = Process::run("supervisorctl restart renbot");

        if ($result->failed()) {
            throw new Exception("Failed to restart bot: {$result->output()}");
        }
    }

    public static function start(): void
    {
        $result = Process::run("supervisorctl start renbot");

        if ($result->failed()) {
            throw new Exception("Failed to start bot: {$result->output()}");
        }
    }

    public static function stop(): void
    {
        $result = Process::run("supervisorctl stop renbot");

        if ($result->failed()) {
            throw new Exception("Failed to stop bot: {$result->output()}");
        }
    }
}
