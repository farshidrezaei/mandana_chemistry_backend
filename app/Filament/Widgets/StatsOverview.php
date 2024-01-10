<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Product;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected int|string|array $columnSpan = [
        'sm' => 5,
        'md' => 2,
        'xl' => 2,
    ];

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            Stat::make('تعداد کارمندان', User::count()),
            Stat::make('تعداد محصول', Product::count()),
            Stat::make('درحال آزمایش', Project::whereFinishedAt(null)->count()),
            Stat::make('آماده تحویل', Project::whereNotNull('finished_at')->whereIsMismatched(false)->count()),
            Stat::make('عدم تطابق', Project::whereNotNull('finished_at')->whereIsMismatched(true)->count()),
            Stat::make('آرشیو شده', Project::onlyTrashed()->count()),
        ];
    }
}
