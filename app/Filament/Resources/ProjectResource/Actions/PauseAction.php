<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Project;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

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
                });
            })
            ->requiresConfirmation()
            ->visible(
                fn (Project $record): bool =>
                    !$record->isPaused()
                    && Auth::user()->can('pause_project_project')
                    && $record->isStarted()
                    && !$record->isFinished()
            );
    }
}
