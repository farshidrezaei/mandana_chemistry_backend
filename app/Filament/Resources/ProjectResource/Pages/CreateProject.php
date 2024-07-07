<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Enums\ProjectStatusEnum;
use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Test;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterCreate(): void
    {
        /** @var Project $project */
        $project = $this->record;

        $tests = $project->product->tests;

        $tests->each(function (Test $test, int $index) use ($project) {
            $project->tests()->attach($test, [
                'order' => $index,
                'started_at' => $index === 0 ? now() : null,
                'finished_at' => null,
                'is_mismatched' => false,
                'renewals_count' => 0,
            ]);
        });
        $project->update(['status' => ProjectStatusEnum::PROCESSING]);
        activity()
            ->event('started')
            ->useLog('projects')
            ->performedOn($project)
            ->causedBy(Auth::user())
            ->log('آزمایش شروع شد.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['started_at'] = now();
        return $data;
    }
}
