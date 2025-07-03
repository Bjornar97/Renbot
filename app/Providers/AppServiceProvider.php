<?php

namespace App\Providers;

use App\Events\AutoPostUpdated;
use App\Jobs\AutoPostCheckJob;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Database\Eloquent\BroadcastableModelEventOccurred;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Nightwatch\Facades\Nightwatch;
use Laravel\Nightwatch\Records\Query;
use Laravel\Nightwatch\Records\QueuedJob;
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
        $this->setNightwatchSampleRate();

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
    }

    private function setNightwatchSampleRate(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        Nightwatch::rejectQueries(function (Query $query) {
            return str_contains($query->sql, 'into `jobs`');
        });

        Nightwatch::rejectQueuedJobs(function (QueuedJob $job) {
            return in_array($job->name, [
                BroadcastableModelEventOccurred::class,
                AutoPostUpdated::class,
                AutoPostCheckJob::class,
            ]);
        });

        $channel = Channel::where('twitch_channel_id', config('services.twitch.channel_id'))->first();

        if ($channel && $channel->is_live) {
            return;
        }

        Nightwatch::dontSample();
    }
}
