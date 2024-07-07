<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class LatestUsersActivity extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'وضعیت امروز کارمندان';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereRelation('roles', 'name', '=', 'lab')
                    ->withCount(
                        [
                            'doingProjects' => fn ($query) => $query->whereDate('created_at', today()),
                            'doneProjects' => fn ($query) => $query->whereDate('created_at', today()),
                            'mismatchedProjects' => fn ($query) => $query->whereDate('created_at', today()),
                        ]
                    )
            )
            ->columns([
                TextColumn::make('name')->label('نام کارمند'),
                TextColumn::make('email')->label('وضعیت الان')->formatStateUsing(
                    fn ($record) => $record->doing_projects_count > 0 ? 'درحال آزمایش' : 'بدون آزمایش'
                ),
                TextColumn::make('doing_projects_count')->label('درحال آزمایش'),
                TextColumn::make('done_projects_count')->label('آماده تحویل'),
                TextColumn::make('mismatched_projects_count')->label('عدم تطابق'),
            ]);
    }


}
