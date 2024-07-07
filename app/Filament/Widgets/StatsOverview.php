<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;
    protected int|string|array $columnSpan = 2;

    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        return [
            Stat::make('درحال آزمایش', Project::whereFinishedAt(null)->count()),
            Stat::make('منطبق', Project::whereNotNull('finished_at')->whereIsMismatched(false)->count()),
            Stat::make('نا منطبق', Project::whereNotNull('finished_at')->whereIsMismatched(true)->count()),
            Stat::make('آرشیو شده', Project::onlyTrashed()->count()),
        ];
    }
    public function authorizeForUser($user, $ability, $arguments = [])
    {
    }
}
