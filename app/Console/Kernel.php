<?php

namespace App\Console;

use App\Console\Commands\NotifyAboutProjects;
use App\Console\Commands\ProjectDetermineStatusCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(ProjectDetermineStatusCommand::class)
            ->everySecond()
            ->withoutOverlapping()
            ->runInBackground();
        $schedule->command(NotifyAboutProjects::class)
            ->everySecond()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('backup:clean')->daily()->at('03:00');
        $schedule->command('backup:run')->daily()->at('03:30');

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
