<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\NotifyAboutProjects;
use App\Console\Commands\ProjectDetermineStatusCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(ProjectDetermineStatusCommand::class)
            ->everyMinute()
            ->everySecond()
            ->withoutOverlapping()
            ->runInBackground();
        $schedule->command(NotifyAboutProjects::class)
            ->everySecond()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
