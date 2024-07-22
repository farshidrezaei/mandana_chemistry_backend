<?php

namespace App\Filament\Resources\ProjectResource\Actions\Bulk;

use App\Models\Project;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ContinueAction extends BulkAction
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('ادامه')
            ->button()
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->color('success')
            ->action(function (Collection $records) {
                $records->each(fn (Project $project) => $project->continue());
                redirect("/admin/projects");
            })
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->visible(Auth::user()->can('continue_project_project'));
    }
}
