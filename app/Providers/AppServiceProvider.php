<?php

namespace App\Providers;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Nightwatch\Facades\Nightwatch;
use Laravel\Nightwatch\Records\Query;
use Laravel\Pennant\Feature;
use Laravel\Pulse\Facades\Pulse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (! app()->environment('testing')) {
            $channel = Channel::where('twitch_channel_id', config('services.twitch.channel_id'))->first();

            if (! $channel || ! $channel->is_live) {
                Log::debug('Dont sample nightwatch');
                Nightwatch::dontSample();
            }
        }

        Feature::resolveScopeUsing(fn ($driver) => null);

        Feature::define('timeouts', fn () => config('app.features.timeouts'));

        Feature::define('bans', fn () => config('app.features.bans'));

        Feature::define('punish-debug', fn () => config('app.features.punish_debug'));

        Feature::define('special-debug', fn () => config('app.features.special_debug'));

        Feature::define('auto-caps-punishment', fn () => config('app.features.auto_caps_punishment'));

        Feature::define('auto-max-emotes-punishment', fn () => config('app.features.auto_max_emotes_punishment'));

        Feature::define('auto-ban-bots', fn () => config('app.features.auto_ban_bots'));

        Pulse::user(fn (User $user) => [
            'name' => $user->username,
            'avatar' => $user->avatar,
        ]);

        if (app()->environment('local') && str_starts_with(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }

        Nightwatch::rejectQueries(function (Query $query) {
            return str_contains($query->sql, 'into `jobs`');
        });
    }
}
