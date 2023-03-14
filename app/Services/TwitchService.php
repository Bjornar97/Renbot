<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use romanzipp\Twitch\Twitch;

class TwitchService
{
    public static function getTwitchId(string $username, User $moderator = null): int
    {
        Log::info("Getting id");

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
                throw new Exception("User does not exist", 404);
            }

            Log::info($result->data());

            return $result->data()[0]->id;
        });

        return $twitchId;
    }
}
