<?php

namespace App\Filament\Resources\NotificationResource\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Notifications\DatabaseNotification;

class MarkAsReadAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('ثبت خوانده شده')
            ->button()
            ->outlined()
            ->color('primary')
            ->action(fn (DatabaseNotification $record, array $data) => $record->update(['read_at' => now()]))
            ->requiresConfirmation()
            ->hidden(
                fn (DatabaseNotification $record): bool => ! is_null($record->read_at)
            );
    }
}
