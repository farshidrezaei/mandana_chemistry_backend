<?php

namespace App\Console\Commands;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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

        Cache::lock("notify_$project->id", 60)
            ->block(60, function () use ($now, $project) {
                $remaining = $project->calculateTimeBeforeNotify();
                if (! is_null($project->getFinishesAt())) {
                    if (Cache::get("project_notify:$project->id", false) === true) {
                        return;
                    }
                    if (((int) $now->diffInMinutes($project->getFinishesAt()?->subMinutes($remaining))) === 0) {
                        $users = User::role(['Sale'])->orWhereIn('id', [$project->user_id])->get();
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

                        Cache::set("project_notify:$project->id", true, 120);
                    }
                }
            });

    }
}
