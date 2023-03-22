<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    public function punish(Request $request)
    {
        Gate::authorize("moderate");

        $data = $request->validate([
            "user" => ["required", "string"],
            "command" => ["required", "string"],
        ]);

        $bot = BotService::bot();

        $bot->on(WelcomeEvent::class, function () use ($data, $bot, $request) {
            Log::info("Punish controller welcome!");
            $twitchUserId = TwitchService::getTwitchId($data['user'], $request->user());
            $command = Command::punishable()->where('command', $data['command'])->first();

            $response = PunishService::user($twitchUserId, $data['user'])
                ->moderator($request->user())
                ->command($command)
                ->bot($bot)
                ->punish();

            Log::info("Punished");

            $bot->say(config("services.twitch.channel"), $response);

            Log::info("Closing");

            $bot->getLoop()->addTimer(3, fn () => $bot->close());
        });

        Log::info("Connecting");

        $bot->connect();

        return response()->json([
            "success" => true,
        ]);
    }
}
