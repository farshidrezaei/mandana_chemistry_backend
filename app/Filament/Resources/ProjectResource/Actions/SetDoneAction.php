<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Test;
use App\Settings\GeneralSettings;
use Filament\Tables\Actions\Action;

class SetDoneAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('پایان موفق')
            ->button()
            ->action(fn(Test $record, array $data) => $record->projectTest->setDone())
            ->requiresConfirmation()
            ->disabled(fn(Test $record): bool => !$record->projectTest->isStarted()
                || $record->projectTest->project->isFinished()
                || $record->projectTest->isFinished()
                || $record->projectTest->isExpired()
            );
    }
}
