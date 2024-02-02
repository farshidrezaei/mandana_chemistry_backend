<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Test;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\ProjectResource\Actions\SetDoneAction;
use App\Filament\Resources\ProjectResource\Actions\RenewalAction;

class TestsRelationManager extends RelationManager
{
    protected static string $relationship = 'tests';
    protected static ?string $inverseRelationship = 'projects';

    protected static ?string $label = 'آزمایش‌ها';
    protected static ?string $modelLabel = 'آزمایش';
    protected static ?string $pluralModelLabel = 'آزمایش‌ها';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('projectTest.order')->label('مرحله')->formatStateUsing(fn ($state) => (int)$state + 1),
                Tables\Columns\TextColumn::make('title')->label('آزمایش'),
                Tables\Columns\TextColumn::make('projectTest.started_at')->label('شروع')->jalaliDate(),
                TextColumn::make('projectTest.test_id')->label('پایان تخمینی')
                    ->formatStateUsing(
                        function (Test $record): string {
                            if ($record->projectTest->isStarted()) {
                                return verta(
                                    $record->started_at->startOfMinute()
                                        ->addMinutes(
                                            $record->duration
                                            + ($record->projectTest->renewals_duration)
                                        )
                                )->format('H:i - Y/m/d');
                            }
                            return '';
                        }
                    ),
                TextColumn::make('user_id')->label('زمان باقی مانده')
                    ->formatStateUsing(function (Test $record): string {
                        if ($record->projectTest->isFinished() || !$record->projectTest->isStarted()) {
                            return "";
                        }

                        return now()->startOfMinute()->diffInMinutes($record->projectTest->getFinishesAt()) . " دقیقه ";
                    }),
                Tables\Columns\TextColumn::make('projectTest.renewals_count')
                    ->formatStateUsing(
                        fn (Test $record): string => "{$record->projectTest->renewals_count}/{$record->projectTest->test->renewals_count}"
                    )
                    ->label('تعداد تمدید'),
                Tables\Columns\TextColumn::make('projectTest.renewals_duration')->label('مقدار تمدید')->suffix(' دقیقه '),
                IconColumn::make('is_mismatched')
                    ->label('وضعیت')
                    ->icon(
                        fn (Test $record): string => $record->projectTest->finished_at
                        ? ($record->projectTest->is_mismatched ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        : ($record->projectTest->started_at ? 'heroicon-o-play-circle' : 'heroicon-o-clock')
                    )
                    ->color(
                        fn (Test $record): string => $record->projectTest->finished_at
                        ? ($record->projectTest->is_mismatched ? 'danger' : 'success')
                        : ($record->projectTest->started_at ? 'info' : 'warning')
                    )
            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
//                Tables\Actions\DeleteAction::make(),
                RenewalAction::make('renewal'),
                SetDoneAction::make('setDone'),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
            ])->poll();
    }
}
