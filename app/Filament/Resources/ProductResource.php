<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Test;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\ProductResource\Pages;
use Filament\Infolists\Components\RepeatableEntry;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([

            TextInput::make('title')
                ->label('نام')
                ->required()
                ->unique(ignoreRecord: true),
            Section::make('آزمایش‌ها')
                ->description('آزمایش های مربوطه را انتخاب کنید. می‌توانید آزمایش هارا مرتب سازی کنید.')
                ->schema([
                    Repeater::make('productTests')
                        ->addActionLabel('افزودن آزمایش')
                        ->label('آزمایش‌ها')
                        ->relationship()
                        ->reorderable()
                        ->required()
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->itemLabel(function (array $state): ?string {
                            $test = Test::find($state['test_id']) ?? null;

                            return $test
                                ? "$test->title ( $test->duration دقیقه)"
                                : null;
                        })
                        ->schema([
                            Select::make('test_id')
                                ->searchable()
                                ->preload()
                                ->label('آزمایش')
                                ->relationship('test', 'title')
                                ->distinct()
                                ->getSearchResultsUsing(
                                    fn (string $search) => Test::where('title', 'like', "%{$search}%")->limit(
                                        50
                                    )->pluck(
                                        'title',
                                        'id'
                                    )
                                )
                                ->required()->native(false),
                        ]),
                ]),

            Hidden::make('user_id')
                ->default(Auth::id())

        ]);
    }

    protected static ?string $navigationGroup = 'تنظیمات';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('title')->label('نام'),

                \Filament\Infolists\Components\Section::make('آزمایش‌ها')
                    ->schema([
                        RepeatableEntry::make('tests')
                            ->label('')
                            ->schema([
                                TextEntry::make('title')->label('نام'),
                                TextEntry::make('duration')->label('مدت انجام آزمایش')->suffix(' دقیقه ')
                            ])->columns(3),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()
                ->label('نام'),

            TextColumn::make('tests_count')->counts('tests')->label('تعداد مراحل'),

            TextColumn::make('tests_sum_duration')
                ->sum('tests', 'duration')
                ->label('مدت زمان انجام')
                ->suffix(' دقیقه '),

        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //  RelationManagers\TestsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return trans('resources.product.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('resources.product.plural');
    }
}
