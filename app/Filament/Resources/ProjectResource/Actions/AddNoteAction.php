<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Project;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

class AddNoteAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('افزودن نوت')
            ->button()
            ->color('primary')
            ->icon('heroicon-o-pencil-square')
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
                        ->label('پیوست')->directory('notes-attachments'),
                ]
            )
            ->action(fn (Project $record, array $data) => $record->addNote($data['body'], $data['attachment']))
            ->requiresConfirmation()
            ->visible(Auth::user()->can('add_project_note_project'));
    }
}
