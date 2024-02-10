<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Project;
use Illuminate\Console\Command;
use App\Settings\GeneralSettings;
use Spatie\Permission\Models\Role;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class NotifyAboutProjects extends Command
{
    protected $signature = 'project:notify';

    protected $description = 'Notify About Projects';

    public function handle(): void
    {
        Project::query()
            ->whereNull('finished_at')
            ->get()
            ->each(fn (Project $project) => $this->notify($project));
    }

    private function notify(Project $project): void
    {
        $this->notifySales($project);
        $this->notifyOwner($project);
    }


    public function notifySales(Project $project): void
    {
        $this->notifyLogic($project, (int)app(GeneralSettings::class)->beforeFinishNotifySaleTime);
    }

    public function notifyOwner(Project $project): void
    {
        $this->notifyLogic($project, (int)app(GeneralSettings::class)->beforeFinishAlertTime);
    }


    public function notifyLogic(Project $project, int $remaining): void
    {
        if (now()->startOfMinute()->diffInMinutes($project->getFinishesAt()->startOfMinute()->subMinutes($remaining)) === 0) {
            $users = User::whereBelongsTo(Role::whereName('Sale')->first())->get();
            $title = " تا" . $remaining . " دقیقه دیگر پروژه" . "«{$project->product->title}»" . " پایان خواهد یافت.";
            $body = "";
            Notification::make()
                ->title($title)
                ->body($body)
                ->actions([
                    Action::make('showNotifications')->label('مشاهده پروژه')
                        ->button()
                        ->url("/admin/projects/$project->id")
                ])
                ->sendToDatabase($users);

            Notification::make()
                ->title($title)
                ->body($body)
                ->actions([
                    Action::make('showNotifications')->label('مشاهده پیغام‌ها')
                        ->button()
                        ->dispatch('open-modal', ['id' => 'database-notifications']),
                    Action::make('showNotifications')->label('مشاهده پروژه')
                        ->button()
                        ->url("/admin/projects/$project->id")

                ])
                ->broadcast($users);
        }
    }

}
