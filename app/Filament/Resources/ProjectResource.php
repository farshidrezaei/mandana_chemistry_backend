<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Actions\Bulk\ContinueAction;
use App\Filament\Resources\ProjectResource\Actions\Bulk\PauseAction;
use App\Filament\Resources\ProjectResource\Actions\PauseAllAction;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers\NotesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\TestsRelationManager;
use App\Models\Note;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use App\Tables\Columns\NewCountDownColumn;
use Ariaieboy\FilamentJalaliDatetimepicker\Forms\Components\JalaliDatePicker;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use JaOcero\ActivityTimeline\Components\ActivityDate;
use JaOcero\ActivityTimeline\Components\ActivityDescription;
use JaOcero\ActivityTimeline\Components\ActivityIcon;
use JaOcero\ActivityTimeline\Components\ActivitySection;
use JaOcero\ActivityTimeline\Components\ActivityTitle;

class ProjectResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        /** @var Project|string $model */
        $model = static::getModel();

        return $model::query()->whereNotNull('started_at')->whereNull('finished_at')->count();
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
            'force_set_done_project_test',
            'force_set_failed_project_test',
            'renewal_project_test',
            'add_project_note',
            'pause_project',
            'pause_all_project',
            'continue_project',
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('product.title')
                    ->formatStateUsing(fn (Model $record): string => $record->title ?? $record->product->title)
                    ->label('نام'),

                TextEntry::make('user.name')
                    ->label('نام کارمند'),

                TextEntry::make('started_at')->label('شروع پروژه')->jalaliDate(),

                TextEntry::make('id')->label('تمدید شده')
                    ->formatStateUsing(
                        fn (Model $record) => ($record->tests->sum('projectTest.renewals_duration').'  دقیقه ')
                    ),

                TextEntry::make('product_id')->label('پایان تخمینی')
                    ->formatStateUsing(
                        fn (Model $record): string => verta(
                            $record->started_at
                                ->addMinutes(
                                    $record->tests->sum('duration') + $record->tests->sum(
                                        'projectTest.renewals_duration'
                                    )
                                )
                        )->format('H:i:s - Y/m/d')
                    ),
                TextEntry::make('finished_at')->label('پایان یافته در')->jalaliDate(),
                IconEntry::make('updated_at')
                    ->label('وضعیت')
                    ->icon(
                        function (Project $record): string {
                            if ($record->isPaused()) {
                                return 'heroicon-o-pause-circle';
                            }

                            return $record->finished_at
                                 ? ($record->is_mismatched ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                                 : 'heroicon-o-play-circle';
                        }
                    )
                    ->color(
                        function (Project $record): string {
                            if ($record->isPaused()) {
                                return 'warning';
                            }

                            return $record->finished_at
                                  ? ($record->is_mismatched ? 'danger' : 'success')
                                  : 'info';
                        }
                    ),
                ActivitySection::make('activities')
                    ->label('اقدامات')
                    //->description('These are the activities that have been recorded.')
                    ->schema([
                        ActivityTitle::make('description')
                            ->getStateUsing(fn ($record) => "<b>$record->description</b>(<i>{$record->causer?->name}</i>)")
                            ->allowHtml(),
                        ActivityDate::make('created_at')
                            ->getStateUsing(fn ($record) => verta($record->created_at)->format(' H:i:s Y/m/d '))
                            ->date('Y/m/d H:i:s', 'Asia/Tehran'),
                        ActivityIcon::make('status')
                            ->icon(fn (?string $state): ?string => match ($state) {
                                'ideation' => 'heroicon-m-light-bulb',
                                'drafting' => 'heroicon-m-bolt',
                                'reviewing' => 'heroicon-m-document-magnifying-glass',
                                'published' => 'heroicon-m-rocket-launch',
                                default => 'heroicon-m-bolt',
                            })
                            ->color(fn (?string $state): ?string => match ($state) {
                                'ideation' => 'purple',
                                'drafting' => 'info',
                                'reviewing' => 'warning',
                                'published' => 'success',
                                default => 'info',
                            }),
                    ])
                    ->columnSpanFull()
                    ->showItemsCount(20)
                    ->showItemsLabel('View Old')
                    ->showItemsIcon('heroicon-m-chevron-down')
                    ->showItemsColor('gray')
                    ->headingVisible(),
                ActivitySection::make('notes')
                    ->label('نوت‌ها')
                    ->schema([
                        ActivityTitle::make('body')
                            ->label('متن')
                            ->allowHtml(),
                        // Be aware that you will need to ensure that the HTML is safe to render, otherwise your application will be vulnerable to XSS attacks.
                        ActivityDescription::make('attachment')
                            ->allowHtml()
                            ->getStateUsing(
                                fn (Note $record) => $record->attachment ? "<a href='".\Storage::url($record->attachment)."' style='color:rgb(192, 132, 252) ' target='_blank'>پیوست</a>" : ''
                            )
                            ->placeholder(''),
                        ActivityDate::make('created_at')
                            ->getStateUsing(fn ($record) => verta($record->created_at)->format(' H:i:s Y/m/d '))
                            ->date('Y/m/d H:i:s', 'Asia/Tehran'),
                        ActivityIcon::make('status')
                            ->icon('heroicon-m-document')
                            ->color('info'),
                    ])
                    ->columnSpanFull()
                    ->showItemsCount(20)
                    ->showItemsLabel('View Old')
                    ->showItemsIcon('heroicon-m-chevron-down')
                    ->showItemsColor('gray')
                    ->headingVisible(),
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

                TextInput::make('title')
                    ->label('نام پروژه'),
                Select::make('extra_time')
                    ->options([
                        10 => 10,
                        15 => 15,
                        20 => 20,
                    ])
                    ->required()
                    ->default(10)
                    ->label('وقت اضافه')->native(false),

                Grid::make(1)
                    ->schema(function (Get $get): array {
                        $product = Product::with('tests')->find($get('product_id'));
                        $duration = $product?->tests->sum('duration') ?? 0;
                        $count = $product?->tests->count() ?? 0;

                        return $product ? [
                            Placeholder::make('employee_number')
                                ->label('')->content(
                                    "در کل $count آزمایش "." در {$duration} دقیقه "
                                ),
                        ] : [];
                    })
                    ->key('dynamicTypeFields'),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([

                TextColumn::make('product.title')->searchable()
                    ->formatStateUsing(fn (Model $record): string => $record->title ?? $record->product->title)
                    ->label('نام پروژه'),

                TextColumn::make('user.name')->searchable()
                    ->label('نام کارمند'),

                TextColumn::make('started_at')->label('شروع پروژه')->jalaliDate(),

                TextColumn::make('id')->label('تمدید شده')
                    ->formatStateUsing(
                        fn (Model $record) => ($record->tests->sum('projectTest.renewals_duration')).' دقیقه '
                    ),

                TextColumn::make('product_id')->label('پایان تخمینی')
                    ->formatStateUsing(
                        fn (Model $record): string => verta(
                            $record->started_at
                                ->addMinutes(
                                    $record->tests->sum('duration') + $record->tests->sum(
                                        'projectTest.renewals_duration'
                                    )
                                )
                        )->format('H:i:s Y-m-d')
                    ),

                TextColumn::make('notes_count')->label('نوت‌ها')->counts('notes')
                    ->badge(),

                NewCountDownColumn::make('user_id')
                    ->label('زمان باقی مانده')
                    ->formatStateUsing(function (Project $record): ?int {
                        if ($record->isFinished() || $record->isExpired()) {
                            return null;
                        }

                        return (int) now()->diffInSeconds($record->getFinishesAt());
                    }),
                TextColumn::make('finished_at')->label('زمان پایان')->jalaliDate(),
                IconColumn::make('updated_at')
                    ->label('وضعیت')
                    ->icon(
                        function (Project $record): string {
                            if ($record->isPaused()) {
                                return 'heroicon-o-pause-circle';
                            }

                            return $record->finished_at
                                ? ($record->is_mismatched ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                                : 'heroicon-o-play-circle';
                        }
                    )
                    ->color(
                        function (Project $record): string {
                            if ($record->isPaused()) {
                                return 'warning';
                            }

                            return $record->finished_at
                                ? ($record->is_mismatched ? 'danger' : 'success')
                                : 'info';
                        }
                    ),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        JalaliDatePicker::make('created_from')->label('از'),
                        JalaliDatePicker::make('created_until')->label('تا'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                SelectFilter::make('product_id')
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->label('محصول')
                    ->relationship('product', 'title')
                    ->getSearchResultsUsing(
                        fn (string $search) => Product::where('title', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('title', 'id')
                    ),
                SelectFilter::make('user_id')
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->label('ایجاد کننده')
                    ->relationship('user', 'name')
                    ->getSearchResultsUsing(
                        fn (string $search) => User::where('name', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                    ),
            ])
            ->actions([
                //                Tables\Actions\ViewAction::make(),
                //                Tables\Actions\EditAction::make(),

            ])
            ->bulkActions([
                //                BulkActionGroup::make([
                ContinueAction::make('bulk-continue'),
                PauseAction::make('bulk-pause'),
                //                ]),
            ]);
        //  ->poll(60);
    }

    public static function getRelations(): array
    {
        return [
            TestsRelationManager::class,
            //            NotesRelationManager::class
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
