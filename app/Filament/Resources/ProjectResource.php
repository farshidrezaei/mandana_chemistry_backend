<?php

namespace App\Filament\Resources;

use App\Models\Test;
use App\Models\Project;
use Filament\Forms\Get;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Infolists\Infolist;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\IconColumn;
use App\Tables\Columns\CountDownColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use App\Filament\Resources\ProjectResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\ProjectResource\RelationManagers\TestsRelationManager;

class ProjectResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        /** @var Project|string $model */
        $model = static::getModel();

        return $model::query()->count();
    }


    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'set_done_project_test',
            'set_failed_project_test',
            'renewal_project_test',
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('product.title')
                    ->label('نام محصول'),

                TextEntry::make('user.name')
                    ->label('نام کارمند'),

                TextEntry::make('started_at')->label('شروع پروژه')->jalaliDate(),

                TextEntry::make('id')->label('تمدید شده')
                    ->formatStateUsing(
                        fn (Model $record) => ($record->tests->sum('projectTest.renewals_duration') . "  دقیقه ")
                    ),

                TextEntry::make('product_id')->label('پایان تخمینی')
                    ->formatStateUsing(
                        fn (Model $record): string => verta(
                            $record->started_at
                                ->addMinutes(
                                    $record->tests->sum('duration') + $record->tests->sum('projectTest.renewals_duration')
                                )
                        )->format('H:i:s - Y/m/d')
                    ),


                IconEntry::make('updated_at')
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


            ])
            ->columns(3);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->searchable()
                    ->preload()
                    ->label('محصول')
                    ->relationship('product', 'title')
                    ->getSearchResultsUsing(
                        fn (string $search) => Product::where('title', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('title', 'id')
                    )->live()
                    ->afterStateUpdated(fn (Select $component) => $component
                        ->getContainer()
                        ->getComponent('dynamicTypeFields')
                        ->getChildComponentContainer()
                        ->fill())
                    ->required()->native(false),

                Grid::make(1)
                    ->schema(function (Get $get): array {
                        $product = Product::with('tests')->find($get('product_id'));
                        return $product?->tests
                            ->map(
                                fn (Test $test, mixed $index) => Placeholder::make('employee_number')->label('')->content(
                                    "مرحله " . ($index + 1) . ": $test->title ($test->duration دقیقه)"
                                )
                            )->toArray()
                            ?? [];
                    })
                    ->key('dynamicTypeFields'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
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
                                ->addMinutes(
                                    $record->tests->sum('duration') + $record->tests->sum('projectTest.renewals_duration')
                                )
                        )->format('H:i:s Y-m-d')
                    ),


                CountDownColumn::make('user_id')->label('زمان باقی مانده')
                    ->formatStateUsing(function (Project $record): ?int {
                        if ($record->isFinished() || $record->isExpired()) {
                            return null;
                        }

                        return now()->diffInSeconds($record->getFinishesAt());
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
            ])
            ->filters([
//                Tables\Filters\TrashedFilter::make()->label('آرشیو')
//                    ->placeholder('آرشیو نشده‌ها')
//                    ->trueLabel('همه')
//                    ->falseLabel('فقط آرشیو شده‌ها')
            ])
            ->actions([
//                Tables\Actions\ViewAction::make(),
//                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
////                    Tables\Actions\DeleteBulkAction::make(),
////                    Tables\Actions\ForceDeleteBulkAction::make(),
////                    Tables\Actions\RestoreBulkAction::make(),
//                ]),
            ]);
        //  ->poll(60);
    }

    public static function getRelations(): array
    {
        return [
            TestsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
//            'index' => Pages\ManageProjects::route('/'),
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('product.tests')
            ->latest()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getModelLabel(): string
    {
        return trans('resources.project.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('resources.project.plural');
    }
}
