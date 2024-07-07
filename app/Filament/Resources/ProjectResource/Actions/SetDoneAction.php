<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Project;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
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

                ]
            )
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->action(fn (Project $record, array $data) => $record->setDone($data))
            ->requiresConfirmation()
            ->hidden(
                fn (Project $record): bool =>
                    !Auth::user()->can('set_done_project_test_project')
                    || !$record->isStarted()
                    || $record->isFinished()
            );
    }
}
