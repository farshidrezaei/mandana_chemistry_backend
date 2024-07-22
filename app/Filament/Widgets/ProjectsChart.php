<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Ariaieboy\FilamentJalaliDatetimepicker\Forms\Components\JalaliDatePicker;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProjectsChart extends ApexChartWidget
{
    protected static ?string $chartId = 'projectsChart';


    protected static ?string $heading = 'نمودار پروژه‌ها';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $loadingIndicator = 'درحال آماده‌سازی...';
    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        if (!$this->readyToLoad) {
            return [];
        }

        $createdFrom = $this->filterFormData['created_from']
            ? Carbon::parse($this->filterFormData['created_from'])->startOfDay()
            : today()->subDays(30);
        $createdUntil = $this->filterFormData['created_until']
            ? Carbon::parse($this->filterFormData['created_until'])->endOfDay()
            : today()->endOfDay();


        $productIds = $this->filterFormData['product_ids'] ?? [];
        $userIds = $this->filterFormData['user_ids'] ?? [];
        $types = $this->filterFormData['types'] ?? [];

        $diff = (int)$createdFrom->diffInDays($createdUntil);

        if ($diff > 0 && $diff <= 30) {
            $period = 'day';
        } elseif ($diff > 31 && $diff <= 365) {
            $period = 'month';
        } else {
            $period = 'year';
        }

        $dates = [];
        $interval = CarbonPeriod::create($createdFrom, "1 $period", $createdUntil);


        foreach ($interval as $date) {
            $dates[ match ($period) {
                'day' => verta($date)->format('d M'),
                'month' => verta($date)->format('Y M'),
                'year' => verta($date)->format('Y'),
            }] = null;

        }

        $groups = Project::query()
            ->when(
                $createdFrom,
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
            )
            ->when(
                $createdUntil,
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
            )
            ->when(
                $productIds,
                fn (Builder $query, $ids): Builder => $query->whereIn('product_id', $ids),
            )
            ->when(
                $userIds,
                fn (Builder $query, $ids): Builder => $query->whereIn('user_id', $ids),
            )
            ->when(
                $types,
                fn (Builder $query, $types): Builder => $query->whereIn('is_mismatched', $types),
            )
            ->get()
            ->groupBy(function (Project $project) use ($period) {
                return match ($period) {
                    'day' => verta($project->created_at)->format('d M'),
                    'month' => verta($project->created_at)->format('Y M'),
                    'year' => verta($project->created_at)->format('Y'),
                };
            });
        foreach ($dates as $key => $_) {
            $dates[$key] = $groups[$key] ?? null;
        }


        $groups = collect($dates);

        $keys = $groups->keys()->toArray();

        $dataSets = [
            'not_mismatched' => [
                'name' => 'منطبق',
                'data' => [],
                'legend' => ['fontFamily' => 'Yekan Bakh FaNum']
            ],
            'mismatched' => [
                'name' => 'نامنطبق',
                'data' => [],
                'legend' => ['fontFamily' => 'Yekan Bakh FaNum']
            ],
        ];
        foreach ($groups as $key => $projects) {

            $dataSets['mismatched']['data'][] = $groups[$key]?->where('is_mismatched', true)->count() ?? 0;
            $dataSets['not_mismatched']['data'][] = $groups[$key]?->where('is_mismatched', false)->count() ?? 0;
        }
        if (count($dataSets['mismatched']['data']) === 0) {
            unset($dataSets['mismatched']);
        }
        if (count($dataSets['not_mismatched']['data']) === 0) {
            unset($dataSets['not_mismatched']);
        }

        sleep(2);
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 400,
                'fontFamily' => 'Yekan Bakh FaNum',
            ],
            'tooltip' => [
        'shared' => true,
          'intersect' => false
        ],
            'legend' => [
                'fontFamily' => 'Yekan Bakh FaNum',
            ],
            'title' => [

                'style' => [
                    'fontFamily' => 'Yekan Bakh FaNum',
                ],
            ],
            'series' => array_values($dataSets),
            'xaxis' => [
                'categories' => $keys,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'Yekan Bakh FaNum',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'Yekan Bakh FaNum',
                    ],
                ],
            ],
            'colors' => ['#BB5EF8','#6A6A6A'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                ],
            ],
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            JalaliDatePicker::make('created_from')->label('از')->default(today()->subDays(7)),
            JalaliDatePicker::make('created_until')->label('تا')->default(today()),
            Select::make('product_id')
                ->searchable()
                ->multiple()
                ->preload()
                ->label('محصول')
                ->native(false)
                ->getSearchResultsUsing(
                    fn (string $search) => Product::where('title', 'like', "%{$search}%")
                        ->limit(50)
                        ->pluck('title', 'id')
                )->options(
                    fn () => Product::limit(50)
                        ->pluck('title', 'id')
                ),
            Select::make('user_id')
                ->searchable()
                ->multiple()
                ->preload()
                ->label('ایجاد کننده')
                ->native(false)
                ->getSearchResultsUsing(
                    fn (string $search) => User::where('name', 'like', "%{$search}%")
                        ->limit(50)
                        ->pluck('name', 'id')
                )->options(
                    fn () => User::limit(50)
                        ->pluck('name', 'id')
                ),
            Select::make('types')
                ->multiple()
                ->label('نوع')
                ->options([
                    '0' => 'منطبق',
                    '1' => 'نامنطبق',

                ]),

        ];
    }
}
