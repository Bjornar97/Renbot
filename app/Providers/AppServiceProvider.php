<?php

namespace App\Providers;

use App\Models\User;
use App\Services\BotService;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Sanctum::ignoreMigrations();

        $this->app->bind(BotService::class, fn() => new BotService());
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Feature::resolveScopeUsing(fn($driver) => null);

        Feature::define("timeouts", fn() => config("app.features.timeouts"));

        Feature::define("bans", fn() => config("app.features.bans"));

        Feature::define("punish-debug", fn() => config("app.features.punish_debug"));

        Feature::define("special-debug", fn() => config("app.features.special_debug"));

        Feature::define("announce-restart", fn() => config("app.features.announce_restart"));

        Feature::define("auto-caps-punishment", fn() => config("app.features.auto_caps_punishment"));

        Feature::define("auto-max-emotes-punishment", fn() => config("app.features.auto_max_emotes_punishment"));

        Feature::define("auto-ban-bots", fn() => config("app.features.auto_ban_bots"));

        Pulse::user(fn(User $user) => [
            'name' => $user->username,
            'avatar' => $user->avatar,
        ]);
    }
}
