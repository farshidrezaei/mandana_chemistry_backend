<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Awcodes\FilamentGravatar\Gravatar;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;
use Phpsa\FilamentPasswordReveal\Password as PasswordInput;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'تنظیمات';

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
            'can_notify_as_sale',
            'can_notify_as_lab',
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                ImageEntry::make('avatar')->getStateUsing(fn ($record) => Gravatar::get($record->email))->circular()->columnSpanFull(),
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('username'),
                TextEntry::make('created_at')->jalaliDate(),
                RepeatableEntry::make('roles')->schema([TextEntry::make('name')]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('نام')->required()->minLength(1)->string(),
                TextInput::make('email')->label('ایمیل')->unique(ignoreRecord: true)->email()->nullable(),
                TextInput::make('username')->label('نام کاربری')->required()->alphaDash()->unique(ignoreRecord: true),
                PasswordInput::make('password')->label('رمز عبور')->password()->rules([
                    Password::min(8),
                ])->required($form->getOperation() === 'create')->dehydrated(fn ($state) => filled($state)),
                Select::make('roles')
                    ->preload()
                    ->native(false)
                    ->label('نقش‌ها')->searchable()->relationship('roles', 'name')->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('id')->label('#')->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('roles.name')->badge()->searchable(),
                TextColumn::make('email'),
                TextColumn::make('username'),
                TextColumn::make('created_at')->jalaliDate(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return trans('resources.user.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('resources.user.plural');
    }
}
