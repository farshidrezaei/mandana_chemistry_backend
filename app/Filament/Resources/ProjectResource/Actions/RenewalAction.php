<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Test;
use Filament\Tables\Actions\Action;

class RenewalAction extends Action
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->label('تمدید')
            ->outlined()
            ->action(fn(Test $record, array $data) => $record->projectTest->renewal())
            ->requiresConfirmation()
            ->disabled(fn(Test $record): bool => !$record->projectTest->isAbleToRenewal());
    }
}
