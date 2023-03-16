<?php

namespace App\Http\Controllers;

use App\Enums\BotStatus;
use App\Services\BotManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Process;
use Inertia\Inertia;
use Throwable;

class BotController extends Controller
{
    public function bot()
    {
        Gate::authorize("moderate");

        $status = BotManagerService::getStatus();

        return Inertia::render("Bot/Show", [
            'status' => $status
        ]);
    }

    public function restart()
    {
        Gate::authorize("moderate");

        try {
            BotManagerService::restart();
            activity()->log("Restarted bot");
        } catch (Throwable $th) {
            return back()->with("error", "Something went wrong when trying to restart the bot. Contact Bjornar97. Error: {$th->getMessage()}");
        }

        return back()->with("success", "The bot has been restarted");
    }

    public function start()
    {
        Gate::authorize("moderate");

        try {
            BotManagerService::start();
            activity()->log("Started bot");
        } catch (\Throwable $th) {
            return back()->with("error", "Something went wrong when trying to start the bot. Contact Bjornar97. Error: {$th->getMessage()}");
        }

        return back()->with("success", "The bot has been started");
    }

    public function stop()
    {
        Gate::authorize("moderate");

        try {
            BotManagerService::stop();
            activity()->log("Stopped bot");
        } catch (\Throwable $th) {
            return back()->with("error", "Something went wrong when trying to stop the bot. Contact Bjornar97. Error: {$th->getMessage()}");
        }

        return back()->with("success", "The bot has been stopped");
    }
}
