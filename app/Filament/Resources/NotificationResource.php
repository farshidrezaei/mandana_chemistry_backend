<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Actions\MarkAsReadAction;
use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Project;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationResource extends Resource
{
    protected static ?string $model = DatabaseNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var Project|string $model */
        $model = static::getModel();

        return $model::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('updated_at')
                    ->formatStateUsing(fn (Model $record): string => $record->data['title'] ?? '')
                    ->label('عنوان'),
                TextEntry::make('type')
                    ->formatStateUsing(fn (Model $record): string => $record->data['body'] ?? '')
                    ->label('متن'),
                TextEntry::make('read_at')->label('زمان خوانده شدن')->jalaliDate(),
                TextEntry::make('created_at')->label('زمان ایجاد شدن')->jalaliDate(),
            ])
            ->columns(3);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([

                TextColumn::make('updated_at')
                    ->formatStateUsing(fn (Model $record): string => $record->data['title'] ?? '')
                    ->label('عنوان'),
                IconColumn::make('notifiable_id')
                    ->label('وضعیت')
                    ->icon(
                        fn (DatabaseNotification $record): string => $record->read_at
                            ? 'heroicon-o-check-circle'
                            : '')
                    ->color(fn (Model $record): string => $record->created_at->startOfDay()->is(now()->startOfDay()) ? 'info' : 'primary'),

                TextColumn::make('notifiable_type')
                    ->formatStateUsing(fn (Model $record): string => $record->created_at->startOfDay() === now()->startOfDay() ? 'امروز' : $record->created_at->diffForHumans())
                    ->color(fn (Model $record): string => $record->created_at->startOfDay()->is(now()->startOfDay()) ? 'info' : 'primary')
                    ->badge()->label('زمان ایجاد'),
                TextColumn::make('type')
                    ->formatStateUsing(fn (Model $record): string => $record->data['body'] ?? '')
                    ->label('متن'),
                TextColumn::make('read_at')->label('زمان خوانده شدن')->jalaliDate(),
                //                TextColumn::make('created_at')->label('زمان ایجاد شدن')->jalaliDate(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                MarkAsReadAction::make('mark_as_read'),
                //                Tables\Actions\EditAction::make(),
                //                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                //                Tables\Actions\BulkActionGroup::make([
                //                    Tables\Actions\DeleteBulkAction::make(),
                //                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNotifications::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::id())
            ->latest()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getModelLabel(): string
    {
        return trans('resources.notification.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('resources.notification.plural');
    }
}
