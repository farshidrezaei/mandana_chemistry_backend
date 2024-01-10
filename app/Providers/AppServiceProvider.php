<?php

namespace App\Providers;

use App\Models\User;
use Filament\Facades\Filament;
use Laravel\Pulse\Facades\Pulse;
use Illuminate\Support\ServiceProvider;
use Filament\Navigation\NavigationGroup;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Pulse::users(function ($ids) {
            return User::findMany($ids)->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'extra' => $user->email,
            ]);
        });

    }
}
