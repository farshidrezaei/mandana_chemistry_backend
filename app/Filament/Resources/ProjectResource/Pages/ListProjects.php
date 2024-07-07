<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Enums\ProjectStatusEnum;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            trans('resources.project.filters.tabs.testing') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()
                    ->where(
                        fn (Builder $query) => $query
                        ->where('status', '!=', ProjectStatusEnum::PAUSED)
                        ->orWhereNull('status')
                    )
                    ->whereNull('finished_at')),

            trans('resources.project.filters.tabs.done') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()
                    ->where(
                        fn (Builder $query) => $query
                        ->where('status', '!=', ProjectStatusEnum::PAUSED)
                        ->orWhereNull('status')
                    )
                    ->whereNotNull('finished_at')
                    ->where('is_mismatched', false)),

            trans('resources.project.filters.tabs.failed') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()
                    ->where(
                        fn (Builder $query) => $query
                        ->where('status', '!=', ProjectStatusEnum::PAUSED)
                        ->orWhereNull('status')
                    )
                    ->whereNotNull('finished_at')
                    ->where('is_mismatched', true)),

            trans('resources.project.filters.tabs.paused') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()
                    ->where('status', ProjectStatusEnum::PAUSED)),

            trans('resources.project.filters.tabs.archived') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
