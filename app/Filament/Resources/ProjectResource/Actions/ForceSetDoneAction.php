<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Project;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ForceSetDoneAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('منطبق زودتر از موعد')
            ->button()
            ->form(
                fn () => [
                    TextInput::make('body')->label('متن')->required()->maxLength(250),
                    FileUpload::make('attachment')
                        ->nullable()
                        ->rules([
                            'nullable',
                            'file',
                            'mimes:jpg,jpeg,png,gif,pdf',
                            'max:5100',
                        ])
                        ->label('پیوست')
                        ->directory('notes-attachments'),

                ]
            )
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->action(function (Project $record, array $data) {
                $record->setDone(true);
                $record->addNote($data['body'], $data['attachment']);
                $this->notify($record, $data);
            })
            ->requiresConfirmation()
            ->hidden(
                fn (Project $record): bool => ! Auth::user()->can('force_set_done_project_test_project')
                    || ! $record->isStarted()
                    || $record->isFinished()
            );
    }

    private function notify(Project $project, array $data): void
    {
        $causer = Auth::user();

        $users = User::role(['admin', 'Sale'])->orWhereIn('id', [$project->user_id])->get();

        $title = $project->title ?? $project->product->title;

        Notification::make()
            ->title("پروژه '{$title}' توسط  '{$causer->name}' به عنوان منطبق زودتر از موعد پایان یافت.")
            ->body($data['body'])
            ->actions([
                \Filament\Notifications\Actions\Action::make('showNotifications')->label('مشاهده پروژه')
                    ->button()
                    ->url("/admin/projects/$project->id"),
            ])
            ->sendToDatabase($users);

        Notification::make()
            ->title("پروژه '{$title}' توسط  '{$causer->name}' به عنوان منطبق زودتر از موعد پایان یافت.")
            ->body($data['body'])
            ->actions([
                \Filament\Notifications\Actions\Action::make('showNotifications')->label('مشاهده پروژه')
                    ->button()
                    ->url("/admin/projects/$project->id"),

            ])
            ->broadcast($users);
    }
}
