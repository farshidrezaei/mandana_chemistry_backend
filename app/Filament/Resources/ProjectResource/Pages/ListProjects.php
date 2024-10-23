<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Enums\ProjectStatusEnum;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\ProjectResource\Actions\PauseAllAction;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->icon('heroicon-o-plus'),
            PauseAllAction::make('pause-all')->icon('heroicon-o-pause-circle'),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();

        return [
            trans('resources.project.filters.tabs.testing') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->withoutTrashed()
                    ->where(
                        fn (Builder $query) => $query
                            ->where('status', '!=', ProjectStatusEnum::PAUSED)
                            ->orWhereNull('status')
                    )
                    ->whereNull('finished_at')),

            trans('resources.project.filters.tabs.done') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->withoutTrashed()
                    ->where(
                        fn (Builder $query) => $query
                            ->where('status', '!=', ProjectStatusEnum::PAUSED)
                            ->orWhereNull('status')
                    )
                    ->whereNotNull('finished_at')
                    ->where('is_mismatched', false)),

            trans('resources.project.filters.tabs.failed') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->withoutTrashed()
                    ->where(
                        fn (Builder $query) => $query
                            ->where('status', '!=', ProjectStatusEnum::PAUSED)
                            ->orWhereNull('status')
                    )
                    ->whereNotNull('finished_at')
                    ->where('is_mismatched', true)),

            trans('resources.project.filters.tabs.paused') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->withoutTrashed()
                    ->where('status', ProjectStatusEnum::PAUSED)),

            trans('resources.project.filters.tabs.archived') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
