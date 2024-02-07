<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;
    protected int|string|array $columnSpan = 'sm';

    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        return [
            Stat::make('درحال آزمایش', Project::whereFinishedAt(null)->count()),
            Stat::make('آماده تحویل', Project::whereNotNull('finished_at')->whereIsMismatched(false)->count()),
            Stat::make('عدم تطابق', Project::whereNotNull('finished_at')->whereIsMismatched(true)->count()),
            Stat::make('آرشیو شده', Project::onlyTrashed()->count()),
        ];
    }
    public function authorizeForUser($user, $ability, $arguments = [])
    {
    }
}
