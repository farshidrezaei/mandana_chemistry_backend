<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\User;
use App\Settings\GeneralSettings;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

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
        //$this->notifyOwner($project);
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

        if (
            (int)now()->diffInSeconds($project->getFinishesAt()?->subSeconds($remaining * 60)) === 0
        ) {
            $users = User::whereHas(
                'roles',
                fn (Builder $roles) => $roles->whereRelation('permissions', 'name', '=', 'can_notify_as_sale_user')
            )
                ->get();
            $title = "آزمایش‌های محصول «{$project->product->title}» تا «{$remaining}» دقیقه دیگر به پایان می‌رسد.";
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
