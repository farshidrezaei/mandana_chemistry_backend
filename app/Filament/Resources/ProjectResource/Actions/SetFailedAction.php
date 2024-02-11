<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Test;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class SetFailedAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('پایان ناموفق')
            ->button()
            ->color('danger')
            ->action(fn (Test $record, array $data) => $record->projectTest->setFailed())
            ->requiresConfirmation()
            ->hidden(
                fn (Test $record): bool =>
                    !Auth::user()->can('set_failed_project_test_project')
                   || (!$record->projectTest->isStarted()
                    || $record->projectTest->project->isFinished()
                    || $record->projectTest->isFinished()
                    || $record->projectTest->isExpired())
            );
    }
}
