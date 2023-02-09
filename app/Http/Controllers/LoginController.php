<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function redirect()
    {
        $scopes = [];

        if (false) {
            // Rendog scopes
            $scopes =
                [
                    "moderation:read",
                    "bits:read",
                    "channel:read:charity",
                    "channel:read:polls",
                    "channel:manage:polls",
                    "channel:read:predictions",
                    "channel:manage:predictions",
                    "channel:read:redemptions",
                    "channel:manage:redemptions",
                    "channel:read:editors",
                    "channel:read:hype_train",
                    "channel:read:subscriptions",
                    "channel:read:vips",
                    "channel:moderate",
                    "channel:manage:schedule",
                ];
        } else {
            $scopes =
                [
                    "moderator:manage:announcements",
                    "moderator:manage:automod",
                    "moderator:read:automod_settings",
                    "moderator:manage:banned_users",
                    "moderator:read:blocked_terms",
                    "moderator:manage:blocked_terms",
                    "moderator:manage:chat_messages",
                    "moderator:manage:chat_settings",
                    "moderator:read:chatters",
                    "moderator:read:followers",
                    "moderator:read:shield_mode",
                    "moderator:manage:shield_mode",
                    "moderator:read:shoutouts",
                    "moderator:manage:shoutouts",
                    "chat:edit",
                    "chat:read"
                ];
        }

        return Socialite::driver('twitch')
            ->scopes($scopes)
            ->redirect();
    }

    public function callback()
    {
        $user = Socialite::driver("twitch")->user();

        dd($user);
    }
}
