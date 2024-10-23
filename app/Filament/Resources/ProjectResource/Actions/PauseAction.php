<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Project;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PauseAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('توقف')
            ->button()
            ->color('warning')
            ->icon('heroicon-o-pause-circle')
            ->form(
                fn () => [
                    TextInput::make('body')->label('متن')->required()->maxLength(100),
                ]
            )
            ->action(function (Project $record, array $data) {
                DB::transaction(function () use ($data, $record) {
                    $record->pause($data['body']);
                    $this->notify($record, $data);
                });
            })
            ->requiresConfirmation()
            ->visible(
                fn (Project $record): bool => ! $record->isPaused()
                    && Auth::user()->can('pause_project_project')
                    && $record->isStarted()
                    && ! $record->isFinished()
            );
    }

    private function notify(Project $project, array $data): void
    {

        $users = User::role(['admin', 'Sale'])->orWhereIn('id', [$project->user_id])->get();
        if ($project->user->isNot(Auth::user())) {
            $users->push(Auth::id());
        }
        $title = $project->title ?? $project->product->title;

        Notification::make()
            ->title("پروژه '{$title}' متوقف شد.")
            ->body($data['body'])
            ->actions([
                \Filament\Notifications\Actions\Action::make('showNotifications')->label('مشاهده پروژه')
                    ->button()
                    ->url("/admin/projects/$project->id"),
            ])
            ->sendToDatabase($users);

        Notification::make()
            ->title("پروژه '{$title}' متوقف شد.")
            ->body($data['body'])
            ->actions([
                \Filament\Notifications\Actions\Action::make('showNotifications')->label('مشاهده پروژه')
                    ->button()
                    ->url("/admin/projects/$project->id"),

            ])
            ->broadcast($users);
    }
}
