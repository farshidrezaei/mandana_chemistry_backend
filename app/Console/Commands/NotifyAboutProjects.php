<?php

namespace App\Console\Commands;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class NotifyAboutProjects extends Command
{
    protected $signature = 'project:notify';

    protected $description = 'Notify About Projects';

    public function handle(): void
    {

        $now = now();
        Project::query()
            ->with('tests')
            ->whereNull('finished_at')
            ->whereNot('status', ProjectStatusEnum::PAUSED)
            ->get()
            ->each(fn (Project $project) => $this->notify($project, $now));
    }

    private function notify(Project $project, Carbon $now): void
    {
        $this->notifySales($project, $now);
        //$this->notifyOwner($project);
    }

    public function notifySales(Project $project, Carbon $now): void
    {
        $this->notifyLogic($project, $now);
    }

    public function notifyOwner(Project $project, Carbon $now): void
    {
        $this->notifyLogic($project, $now);
    }

    public function notifyLogic(Project $project, Carbon $now): void
    {
        $remaining = $project->calculateTimeBeforeNotify();
        if (! is_null($project->getFinishesAt())) {
            if (((int) $now->diffInMinutes($project->getFinishesAt()?->subMinutes($remaining))) === 0) {
                $users = User::role(['Sale'])->get()->push($project->user);
                $title = "آزمایش‌های محصول «{$project->product->title}» تا «{$remaining}» دقیقه دیگر به پایان می‌رسد.";
                $body = '';
                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->actions([
                        Action::make('showNotifications')->label('مشاهده پروژه')
                            ->button()
                            ->url("/admin/projects/$project->id"),
                    ])
                    ->sendToDatabase($users);

                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->actions([
                        Action::make('showNotifications')->label('مشاهده پروژه')
                            ->button()
                            ->url("/admin/projects/$project->id"),

                    ])
                    ->broadcast($users);
            }
        }
    }
}
