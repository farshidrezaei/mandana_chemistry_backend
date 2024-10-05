<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Enums\ProjectStatusEnum;
use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Test;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterCreate(): void
    {
        /** @var Project $project */
        $project = $this->record;

        $tests = $project->product->tests;

        $tests->each(function (Test $test, int $index) use ($project) {
            $project->tests()->attach($test, [
                'order' => $index,
                'started_at' => $index === 0 ? now() : null,
                'finished_at' => null,
                'is_mismatched' => false,
                'renewals_count' => 0,
            ]);
        });
        $project->update(['status' => ProjectStatusEnum::PROCESSING]);
        activity()
            ->event('started')
            ->useLog('projects')
            ->performedOn($project)
            ->causedBy(Auth::user())
            ->log('آزمایش شروع شد.');

        $this->notify();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['started_at'] = now();

        return $data;
    }

    private function notify(): void
    {
        /** @var Project $project */
        $project = $this->record;

        $users = User::role(['Sale'])->get()->push($project->user);

        $title = $project->title ?? $project->product->title;

        Notification::make()
            ->title("پروژه جدیدی با عنوان {$title} ایجاد شد.")
            ->actions([
                Action::make('showNotifications')->label('مشاهده پروژه')
                    ->button()
                    ->url("/admin/projects/$project->id"),
            ])
            ->sendToDatabase($users);

        Notification::make()
            ->title($title)
            ->actions([
                Action::make('showNotifications')->label('مشاهده پروژه')
                    ->button()
                    ->url("/admin/projects/$project->id"),

            ])
            ->broadcast($users);
    }
}
