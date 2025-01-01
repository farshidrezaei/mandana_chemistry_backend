<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Project;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArchiveAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('آرشیو')
            ->button()
            ->outlined()
            ->color('warning')
            ->icon('heroicon-o-archive-box')
            ->form(
                fn () => [
                    TextInput::make('body')->label('متن')->required()->maxLength(250),
                ]
            )
            ->action(function (Project $record, array $data) {
                DB::transaction(function () use ($data, $record) {
                    $record->delete();
                    $this->notify($record, $data);
                    activity()
                        ->event('archive')
                        ->useLog('projects')
                        ->performedOn($record)
                        ->causedBy(Auth::user())
                        ->log(
                            ' پروژه '
                            .'توسط '
                            .Auth::user()->name
                            .' آرشیو شد. '
                            .'متن آرشیو: '
                            .$data['body']
                        );
                });
            })
            ->requiresConfirmation()
            ->visible(
                fn (Project $record): bool => ! $record->isPaused()
                    && $record->isStarted()
                    && $record->isFinished()
                    && ! $record->trashed()
            );
    }

    private function notify(Project $project, array $data): void
    {

        $users = User::role(['Admin', 'Lab'])->role(['admin'])->orWhereIn('id', [$project->user_id])->get();
        if ($project->user->isNot(Auth::user())) {
            $users->push(Auth::user());
        }
        $title = $project->title ?? $project->product->title;

        Notification::make()
            ->title(

                ' پروژه '
                ." '{$title}'"
                .'توسط '
                .Auth::user()->name
                .' آرشیو شد. '
            )
            ->body($data['body'])
            ->actions([
                \Filament\Notifications\Actions\Action::make('showNotifications')->label('مشاهده پروژه')
                    ->button()
                    ->url("/admin/projects/$project->id"),
            ])
            ->sendToDatabase($users);

        Notification::make()
            ->title(
                ' پروژه '
                ." '{$title}'"
                .'توسط '
                .Auth::user()->name
                .' آرشیو شد. '
            )
            ->body($data['body'])
            ->actions([
                \Filament\Notifications\Actions\Action::make('showNotifications')->label('مشاهده پروژه')
                    ->button()
                    ->url("/admin/projects/$project->id"),

            ])
            ->broadcast($users);
    }
}
