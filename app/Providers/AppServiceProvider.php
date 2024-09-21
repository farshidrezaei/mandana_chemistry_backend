<?php

namespace App\Providers;

use App\Models\User;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        DatabaseNotifications::macro('getNotificationsQuery', function () {
            if (! $this->isPaginated()) {
                /** @phpstan-ignore-next-line */
                return $this->getNotificationsQuery()->get();
            }

            return $this->getNotificationsQuery()->simplePaginate(10, pageName: 'database-notifications-page');
        });
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Pulse::users(function ($ids) {
            return User::findMany($ids)->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'extra' => $user->email,
            ]);
        });


    }
}
