<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];


    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(fn() => Auth::user()->username === 'super-admin');
        Gate::define('viewPulse', function (User $user) {
            return true;
        });
    }
}
