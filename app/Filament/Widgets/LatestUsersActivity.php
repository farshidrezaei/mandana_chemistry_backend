<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestUsersActivity extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'وضعیت امروز کارمندان';


    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->withCount('doingProjects', 'doneProjects', 'mismatchedProjects'))
            ->columns([
                TextColumn::make('name')->label('نام کارمند'),
                TextColumn::make('email')->label('وضعیت الان')->formatStateUsing(
                    fn($record) =>$record->doing_projects_count > 0 ? 'درحال آزمایش' : 'بدون آزمایش'
                ),
                TextColumn::make('doing_projects_count')->label('درحال آزمایش'),
                TextColumn::make('done_projects_count')->label('آماده تحویل'),
                TextColumn::make('mismatched_projects_count')->label('عدم تطابق'),
            ]);
    }

}
