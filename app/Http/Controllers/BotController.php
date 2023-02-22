<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Inertia\Inertia;

class BotController extends Controller
{
    public function bot()
    {
        return Inertia::render("Bot/Show");
    }

    public function restart()
    {
        $result = Process::run("supervisorctl restart renbot");

        if ($result->failed()) {
            return back()->with("error", "Something went wrong when trying to restart the bot. Contact Bjornar97. Error: {$result->output()}");
        }

        return back()->with("success", "The bot is getting restarted!");
    }
}
