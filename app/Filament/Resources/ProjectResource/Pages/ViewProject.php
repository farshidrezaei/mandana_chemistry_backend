<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\ProjectResource\Actions\AddNoteAction;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\EditAction::make(),
            AddNoteAction::make('add-note'),
            ProjectResource\Actions\ArchiveAction::make('archive'),
            ProjectResource\Actions\SetDoneAction::make('set-done'),
            ProjectResource\Actions\SetFailedAction::make('set-failed'),
            ProjectResource\Actions\ForceSetDoneAction::make('force-set-done'),
            ProjectResource\Actions\ForceSetFailedAction::make('force-set-failed'),
            ProjectResource\Actions\PauseAction::make('set-paused'),
            ProjectResource\Actions\ContinueAction::make('set-continued'),
        ];
    }
}
