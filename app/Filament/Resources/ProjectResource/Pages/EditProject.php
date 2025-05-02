<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            //            Actions\DeleteAction::make(),
            //            Actions\ForceDeleteAction::make(),
            //            Actions\RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        activity()
            ->event('edit')
            ->useLog('projects')
            ->performedOn($this->record)
            ->causedBy(Auth::user())
            ->log(
                'نام پروژه '
                .'توسط '
                .Auth::user()->name
                .' تغییر کرد. '
            );
    }
}
