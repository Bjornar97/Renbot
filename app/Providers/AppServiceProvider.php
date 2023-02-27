<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
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
        Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Feature::define("timeouts", fn () => config("app.features.timeouts"));

        Feature::define("bans", fn () => config("app.features.bans"));

        Feature::define("punish-debug", fn () => config("app.features.punish_debug"));

        Feature::define("special-debug", fn () => config("app.features.special_debug"));
    }
}
