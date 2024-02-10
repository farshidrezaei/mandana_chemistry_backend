<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Product;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class SuperAdminStatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected int|string|array $columnSpan = 2;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            Stat::make('تعداد کارمندان', User::count()),
            Stat::make('تعداد محصول', Product::count()),
            Stat::make('تعداد پروژه', Project::count()),
        ];
    }
}
