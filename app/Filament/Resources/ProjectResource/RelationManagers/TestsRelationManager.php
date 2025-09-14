<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\ProjectResource\Actions\ProjectTest\PassTestAction;
use App\Filament\Resources\ProjectResource\Actions\ProjectTest\RenewalAction;
use App\Models\Test;
use App\Services\Utils\Utils;
use App\Tables\Columns\NewCountDownColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                Tables\Columns\TextColumn::make('projectTest.order')->label('مرحله')->formatStateUsing(fn ($state) => (int) $state + 1),
                Tables\Columns\TextColumn::make('title')->label('آزمایش'),
                Tables\Columns\TextColumn::make('projectTest.started_at')->label('شروع')->jalaliDate(),
                TextColumn::make('projectTest.test_id')->label('پایان تخمینی')
                    ->formatStateUsing(
                        function (Test $record): string {
                            if ($record->projectTest->isStarted()) {
                                return verta(
                                    $record->projectTest->started_at
                                        ->addMinutes(
                                            $record->duration
                                            + ($record->projectTest->renewals_duration)
                                        )
                                )->format('H:i:s - Y/m/d');
                            }

                            return '';
                        }
                    ),

                TextColumn::make('user_id')
                    ->label('زمان باقی مانده')
                    ->formatStateUsing(function (Test $record): ?string {
                        $finishedAt = $record->projectTest->getFinishesAt();
                        if (! $finishedAt) {
                            return '-';
                        }
                        $remaining = $record->projectTest->getRemainingSeconds();

                        return $remaining > 0
                            ? Utils::formatDuration($remaining)
                            : '-';
                    }),
                //                NewCountDownColumn::make('user_id')->label('زمان باقی مانده')
                //                    ->formatStateUsing(function (Test $record): ?int {
                //                        $finishedAt = $record->projectTest->getFinishesAt();
                //                        if (! $finishedAt) {
                //                            return null;
                //                        }
                //
                //                        return $record->projectTest->getRemainingSeconds();
                //                    }),

                Tables\Columns\TextColumn::make('projectTest.renewals_count')
                    ->formatStateUsing(
                        fn (Test $record): string => "{$record->projectTest->renewals_count}"
                    )
                    ->label('تعداد تمدید'),
                Tables\Columns\TextColumn::make('projectTest.renewals_duration')->label('مقدار تمدید')->suffix(' دقیقه '),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                RenewalAction::make('renewal'),
                PassTestAction::make('pass')->after(function () {
                    $this->js('window.location.reload();');
                }),
            ])
            ->bulkActions([
                //                Tables\Actions\BulkActionGroup::make([
                //                    Tables\Actions\DeleteBulkAction::make(),
                //                ]),
            ])->poll('30s')
            ->headerActions([
                Action::make('refresh')
                    ->label('بروزرسانی')
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn () => $this->resetTable()) // trigger Livewire re-render
                    ->color('gray'),
            ])
            ->paginationPageOptions([5, 10, 15, 20, 30]);
    }
}
