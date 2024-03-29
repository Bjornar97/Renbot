<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SingleChatMessageJob;
use App\Models\Command;
use App\Services\BotService;
use App\Services\PunishService;
use App\Services\TwitchService;
use GhostZero\Tmi\Events\Irc\WelcomeEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PunishController extends Controller
{
    public function punishableCommands(Request $request)
    {
        Gate::authorize("moderate");

        return response()->json(Command::punishable()->active()->orderBy("command")->get());
    }

    public function punish(Request $request)
    {
        Gate::authorize("moderate");

        $data = $request->validate([
            "user" => ["required", "string"],
            "command" => ["required", "string"],
        ]);

        $twitchUserId = TwitchService::getTwitchId($data['user'], $request->user());
        $command = Command::punishable()->where('command', $data['command'])->first();

        $response = PunishService::user($twitchUserId, $data['user'])
            ->moderator($request->user())
            ->command($command)
            ->punish();

        SingleChatMessageJob::dispatch($response);

        return response()->json([
            "success" => true,
        ]);
    }
}
