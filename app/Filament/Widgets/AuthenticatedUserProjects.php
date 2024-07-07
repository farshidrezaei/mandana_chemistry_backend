<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Tables\Columns\NewCountDownColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class AuthenticatedUserProjects extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'پروژه‌های من';


    public function table(Table $table): Table
    {
        return $table
            ->query(Project::query()->whereBelongsTo(Auth::user())->withCount('tests')->latest())
            ->columns([
                TextColumn::make('product.title')->searchable()
                    ->label('نام محصول'),

                TextColumn::make('user.name')->searchable()
                    ->label('نام کارمند'),

                TextColumn::make('started_at')->label('شروع پروژه')->jalaliDate(),

                TextColumn::make('id')->label('تمدید شده')
                    ->formatStateUsing(fn (Model $record) => ($record->tests->sum('projectTest.renewals_duration')) . " دقیقه "),

                TextColumn::make('product_id')->label('پایان تخمینی')
                    ->formatStateUsing(
                        fn (Model $record): string => verta(
                            $record->started_at
                                ->addMinutes($record->tests->sum('duration') + $record->tests->sum('projectTest.renewals_duration'))
                        )->format('H:i:s Y-m-d')
                    ),


                NewCountDownColumn::make('user_id')->label('زمان باقی مانده')
                    ->formatStateUsing(function (Project $record): ?int {
                        if ($record->isFinished()) {
                            return null;
                        }

                        return (int)now()->diffInSeconds($record->getFinishesAt());
                    }),
                TextColumn::make('finished_at')->label('زمان پایان')->jalaliDate(),
                IconColumn::make('updated_at')
                    ->label('وضعیت')
                    ->icon(
                        fn (Project $record): string => $record->finished_at
                            ? ($record->is_mismatched ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                            : 'heroicon-o-play-circle'
                    )
                    ->color(
                        fn (Project $record): string => $record->finished_at
                            ? ($record->is_mismatched ? 'danger' : 'success')
                            : 'info'
                    ),
            ])->actions([
                ViewAction::make()->url(fn (Project $record) => "/admin/projects/$record->id"),

            ]);
    }

}
