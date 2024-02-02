<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Models\Test;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ProjectResource;

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
                'started_at' => $index === 0 ? now()->startOfMinute() : null,
                'finished_at' => null,
                'is_mismatched' => false,
                'renewals_count' => 0,
            ]);
        });
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['started_at'] = now()->startOfMinute();
        return $data;
    }
}
