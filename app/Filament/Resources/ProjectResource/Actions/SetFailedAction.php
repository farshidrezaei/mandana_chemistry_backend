<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Project;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class SetFailedAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('نامنطبق است')
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
                            'max:5100'
                        ])
                        ->label('پیوست')
                        ->directory('notes-attachments'),
                ]
            )
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->action(fn (Project $record, array $data) => $record->setFailed($data))
            ->requiresConfirmation()
            ->hidden(
                fn (Project $record): bool =>
                    !Auth::user()->can('set_failed_project_test_project')
                    || !$record->isStarted()
                    || $record->isFinished()
            );
    }
}
