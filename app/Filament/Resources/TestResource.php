<?php

namespace App\Filament\Resources;

use App\Models\Test;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\TestResource\Pages;
use Filament\Infolists\Components\RepeatableEntry;

class TestResource extends Resource
{
    protected static ?string $model = Test::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'تنظیمات';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('نام')
                    ->required()
                    ->unique(ignoreRecord: true)->columnSpanFull(),
                TextInput::make('duration')
                    ->label('مدت زمان انجام')
                    ->type('number')
                    ->suffix('دقیقه')->columnSpanFull(),
                TextInput::make('renewals_count')
                    ->label('دفعات تمدید')
                    ->type('number')
                    ->columnSpanFull(),
                Hidden::make('user_id')
                    ->default(Auth::id())
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()
                    ->label('نام'),
                Tables\Columns\TextColumn::make('duration')
                    ->label('مدت زمان انجام')
                    ->suffix(' دقیقه '),
                Tables\Columns\TextColumn::make('renewals_count')->label('دفعات تمدید')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modalWidth('sm'),
                Tables\Actions\DeleteAction::make()->modalWidth('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->modalWidth('sm'),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('title')->label('نام'),

                Section::make('این آزمایش در این محصول‌ها استفاده شده است')
                    ->schema([
                        RepeatableEntry::make('products')
                            ->label('محصول‌ها')
                            ->schema([
                                TextEntry::make('title')->label('نام'),
                            ])->columns(1),
                    ]),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTests::route('/'),
            'view' => Pages\ViewTest::route('/{record}'),

        ];
    }

    public static function getModelLabel(): string
    {
        return trans('resources.test.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('resources.test.plural');
    }
}
