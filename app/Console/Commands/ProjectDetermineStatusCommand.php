<?php

namespace App\Console\Commands;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\Test;
use App\Settings\GeneralSettings;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class ProjectDetermineStatusCommand extends Command
{
    protected $signature = 'project:determine-status';

    protected $description = 'Determine Project Status';

    public function handle(): void
    {
        Project::query()
            ->with('tests')
            ->whereNull('finished_at')
            ->where('status', '!=', ProjectStatusEnum::PAUSED)
            ->get()
            ->each(fn (Project $project) => $this->determineProjectTests($project));
    }

    private function determineProjectTests(Project $project): void
    {
        $tests = $project->tests;
        $unFinishedTests = $tests->whereNotNull('projectTest.started_at')->whereNull('projectTest.finished_at');
        if ($unFinishedTests->isNotEmpty()) {
            foreach ($unFinishedTests as $test) {
                if ($test->projectTest->isExpired()) {
                    $test->projectTest->update([
                        'finished_at' => now(),
                        'is_mismatched' => false,
                    ]);

                    $unFinishedTests = $project
                        ->tests()
                        ->get()
                        ->whereNull('projectTest.finished_at')
                        ->sortBy('projectTest.order');

                    $nextTest = $unFinishedTests->first();
                    if ($nextTest) {
                        $nextTestIndex = $nextTest->projectTest->order + 1;
                        $nextTest->projectTest
                            ->update([
                                'started_at' => now(),
                                'is_mismatched' => false,
                            ]);
                        activity()
                            ->event('next-step')
                            ->useLog('projects')
                            ->performedOn($project)
                            ->causedBy(Auth::user())
                            ->log(" آزمایش وارد مرحله $nextTestIndex  شد. ");
                    } else {
                        activity()
                            ->event('finished')
                            ->useLog('projects')
                            ->performedOn($project)
                            ->causedBy(Auth::user())
                            ->log(' آزمایش تمام تمام شد. ');
                    }
                } else {
                    $this->notifyOwner($test);
                }
            }
        }
    }

    private function notifyOwner(Test $test): void
    {
        $remaining = app(GeneralSettings::class)->beforeFinishAlertTime;

        $project = $test->projectTest->project;
        if (
            $project->user->can('can_notify_as_lab_user')
            && (! $test->projectTest->has_been_notified)
            && (int) now()->diffInSeconds(
                $test->projectTest
                    ->getFinishesAt()
                    ->subSeconds($remaining * 60)
            ) === 0
        ) {
            $title = "مرحله «{$test->title}» آزمایش محصول «{$project->product->title}» تا «{$remaining}» دقیقه دیگر به پایان می‌رسد";
            $body = '';
            Notification::make()
                ->title($title)
                ->body($body)
                ->actions([
                    Action::make('showNotifications')->label('مشاهده پروژه')
                        ->button()
                        ->url("/admin/projects/$project->id"),
                ])
                ->sendToDatabase([$project->user]);

            Notification::make()
                ->title($title)
                ->body($body)
                ->actions([
                    /*Action::make('showNotifications')->label('مشاهده پیغام‌ها')
                        ->button()
                        ->dispatch('open-modal', ['id' => 'database-notifications']),*/
                    Action::make('showNotifications')->label('مشاهده پروژه')
                        ->button()
                        ->url("/admin/projects/$project->id"),

                ])
                ->broadcast([$project->user]);
            $test->projectTest->setAsNotified();
        }
    }
}
