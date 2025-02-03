<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('rendog', function (User $user) {
            return $user->type === 'rendog';
        });

        Gate::define('moderate', function (User $user) {
            return $user->can('rendog') || $user->type === 'moderator';
        });

        Gate::define('admin', function (User $user) {
            return $user->username === 'Bjornar97';
        });

        Gate::define('viewPulse', function (User $user) {
            return $user->can('admin');
        });
    }
}
