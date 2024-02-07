<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class LabStatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected int|string|array $columnSpan = 'sm';

    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        return [
            Stat::make('درحال آزمایش من', Project::whereFinishedAt(null)->whereBelongsTo(Auth::user())->count()),
            Stat::make(
                'موفق امروز من',
                Project::whereNotNull('finished_at')
                    ->whereIsMismatched(false)
                    ->whereBelongsTo(Auth::user())
                    ->whereDate('created_at', today())
                    ->count()
            ),
            Stat::make(
                'ناموفق امروز من',
                Project::whereNotNull('finished_at')
                    ->whereIsMismatched(true)
                    ->whereBelongsTo(Auth::user())
                    ->whereDate('created_at', today())
                    ->count()
            ),
        ];
    }
}
