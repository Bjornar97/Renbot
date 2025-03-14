<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as TwoUser;
use romanzipp\Twitch\Twitch;
use SocialiteProviders\Twitch\Provider;

class LoginController extends Controller
{
    /** @var list<string> */
    public array $regularScopes = [
        'user:read:follows',
        'user:read:subscriptions',
    ];

    /** @var list<string> */
    public array $moderatorScopes = [
        'moderator:manage:announcements',
        'moderator:manage:automod',
        'moderator:read:automod_settings',
        'moderator:manage:banned_users',
        'moderator:read:blocked_terms',
        'moderator:manage:blocked_terms',
        'moderator:read:chat_messages',
        'moderator:manage:chat_messages',
        'moderator:manage:chat_settings',
        'moderator:read:chatters',
        'moderator:read:followers',
        'moderator:read:shield_mode',
        'moderator:manage:shield_mode',
        'moderator:read:shoutouts',
        'moderator:manage:shoutouts',
        'moderator:manage:warnings',
        'chat:edit',
        'user:write:chat',
        'chat:read',
        'user:read:moderated_channels',
        'moderator:read:moderators',
        'moderator:read:vips',
        'moderator:manage:unban_requests',
        'moderator:read:suspicious_users',
    ];

    /** @var list<string> */
    public array $rendogScopes = [
        'moderation:read',
        'bits:read',
        'channel:bot',
        'channel:read:charity',
        'channel:read:polls',
        'channel:manage:polls',
        'channel:read:predictions',
        'channel:manage:predictions',
        'channel:read:redemptions',
        'channel:manage:redemptions',
        'channel:read:editors',
        'channel:read:hype_train',
        'channel:read:subscriptions',
        'channel:read:vips',
        'channel:moderate',
        'channel:manage:schedule',
    ];

    public function login(Request $request): Response|RedirectResponse
    {
        if (! auth()->check()) {
            return Inertia::render('Login', [
                'isBot' => $request->query('is_bot'),
            ]);
        }

        if (Gate::allows('moderate')) {
            return redirect()->route('commands.index');
        }

        return redirect()->route('home');
    }

    public function redirect(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'string'],
            'isBot' => ['nullable', 'string'],
        ]);

        $role = $data['role'];

        $scopes = $this->regularScopes;

        if ($role === 'moderator') {
            $scopes = $this->moderatorScopes;
        }

        if ($role === 'rendog') {
            $scopes = $this->rendogScopes;
        }

        if ($data['isBot'] ?? false) {
            $scopes[] = 'user:bot';
            $scopes[] = 'user:read:chat';
        }

        /** @var Provider $twitch */
        $twitch = Socialite::driver('twitch');

        return $twitch
            ->scopes($scopes)
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        /** @var TwoUser $user */
        $user = Socialite::driver('twitch')->user();

        $accessExpriesAt = now()->addSeconds($user->expiresIn);

        $type = $this->getType($user);

        $user = User::updateOrCreate([
            'twitch_id' => (int) $user->id,
        ], [
            'username' => $user->name,
            'avatar' => $user->avatar,
            'type' => $type,
            'twitch_access_token' => $user->token,
            'twitch_refresh_token' => $user->refreshToken,
            'twitch_access_token_expires_at' => $accessExpriesAt,
        ]);

        if ($user->disabled_at) {
            return redirect()
                ->route('login')
                ->with('error', 'Your account is unfortunately deactivated, contact Bjornar97 if this is a mistake');
        }

        Auth::login($user);

        $route = 'rules';

        if (Gate::allows('moderate')) {
            $route = 'commands.index';
        }

        return redirect()->intended(route($route))->with('success', 'You successfully logged in!');
    }

    private function getType(TwoUser $user): string
    {
        $twitch = new Twitch;

        $twitch->withToken($user->token);

        $result = $twitch->get('moderation/channels', [
            'user_id' => $user->id,
            'first' => 100,
        ]);

        $channels = collect($result->data());

        $type = 'viewer';

        if ($channels->where('broadcaster_id', config('services.twitch.channel_id'))->isNotEmpty()) {
            $type = 'moderator';
        }

        if (strtolower($user->name) === 'rendogtv') {
            $type = 'rendog';
        }

        return $type;
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        return redirect('/')->with('success', 'You successfully logged out');
    }
}
