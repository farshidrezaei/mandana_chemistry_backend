<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Test;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class SetDoneAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('پایان موفق')
            ->button()
            ->color('success')
            ->action(fn (Test $record, array $data) => $record->projectTest->setDone())
            ->requiresConfirmation()
            ->hidden(
                fn (Test $record): bool =>
                    !Auth::user()->can('set_done_project_test_project')
                    || (!$record->projectTest->isStarted()
                    || $record->projectTest->project->isFinished()
                    || $record->projectTest->isFinished()
                    || $record->projectTest->isExpired())
            );
    }
}
