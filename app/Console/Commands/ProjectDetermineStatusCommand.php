<?php

namespace App\Console\Commands;

use App\Models\Test;
use App\Models\Project;
use Illuminate\Console\Command;
use App\Settings\GeneralSettings;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class ProjectDetermineStatusCommand extends Command
{
    protected $signature = 'project:determine-status';

    protected $description = 'Determine Project Status';

    public function handle(): void
    {
        Project::query()
            ->with('tests')
            ->whereNull('finished_at')
            ->get()
            ->each(fn (Project $project) => $this->determineProject($project));
    }

    private function determineProject(Project $project): void
    {
        $tests = $project->tests;
        $unFinishedTests = $tests->whereNotNull('projectTest.started_at')->whereNull('projectTest.finished_at');
        if ($unFinishedTests->isNotEmpty()) {
            foreach ($unFinishedTests as $test) {
                $this->handleTest($test);
            }
        } else {
            $tests->filter(fn (Test $test) => $test->projectTest->isMismatched())->isEmpty()
                ? $project->update([
                'finished_at' => now(),
                'is_mismatched' => false
            ])
                : $project->update([
                'finished_at' => now(),
                'is_mismatched' => true
            ]);
        }
    }

    private function handleTest(Test $test): void
    {
        if ($test->projectTest->isExpired()) {
            $test->projectTest->setDone();
        } else {
            $remaining = app(GeneralSettings::class)->beforeFinishAlertTime;
            $project = $test->projectTest->project;
            if (
                $project->user->can('can_notify_as_sale_user')
                && (!$test->projectTest->has_been_notified)
                && now()->diffInMinutes(
                    $test->projectTest
                        ->getFinishesAt()
                        ->subMinutes($remaining) === 0
                )
            ) {
                $title = "مرحله «{$test->title}» آزمایش محصول «{$project->product->title}» تا «{$remaining}» دقیقه دیگر به پایان می‌رسد";
                $body = "";
                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->actions([
                        Action::make('showNotifications')->label('مشاهده پروژه')
                            ->button()
                            ->url("/admin/projects/$project->id")
                    ])
                    ->sendToDatabase([$project->user]);

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
                    ->broadcast([$project->user]);
                $test->projectTest->update(['has_been_notified' => true]);
            }
        }
    }
}
