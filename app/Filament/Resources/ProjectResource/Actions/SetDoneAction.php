<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Project;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

class SetDoneAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('منطبق است')
            ->button()
            ->form(
                fn () => [
                    TextInput::make('body')->label('متن')->required()->maxLength(100),
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
                $record->setDone();
                $record->addNote($data['body'], $data['attachment']);
            })
            ->requiresConfirmation()
            ->hidden(
                fn (Project $record): bool => ! Auth::user()->can('set_done_project_test_project')
                    || ! $record->isStarted()
                    || $record->isFinished()
            );
    }
}
