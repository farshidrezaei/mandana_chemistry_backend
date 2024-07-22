<?php

namespace App\Filament\Resources\ProjectResource\Actions\Bulk;

use App\Models\Project;
use Filament\Forms\Components\TextInput;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PauseAction extends BulkAction
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
            ->action(
                function (Collection $records, array $data) {
                    $records->each(fn (Project $project) => $project->pause($data['body']));
                    redirect("/admin/projects");
                }
            )
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation()
            ->visible(Auth::user()->can('pause_project_project'));
    }
}
