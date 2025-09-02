<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Project;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class ContinueAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('ادامه')
            ->button()
            ->color('primary')
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->action(function (Project $record) {
                $record->continue();
            })
            ->requiresConfirmation()
            ->visible(
                fn (Project $record): bool => $record->isPaused()
                    && Auth::user()->can('continue_project_project')
                    && $record->isStarted()
                    && ! $record->isFinished()
            );
    }
}
