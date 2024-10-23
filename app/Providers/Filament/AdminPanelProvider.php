<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use Awcodes\FilamentGravatar\GravatarPlugin;
use Awcodes\FilamentGravatar\GravatarProvider;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Filament\Notifications\Livewire\Notifications;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\TextColumn;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->colors(['primary' => Color::Purple])
            ->font('Yekan Bakh FaNum', asset('/fonts/yekan_bakh_fa/Webfonts/fontiran.css'), LocalFontProvider::class)
            // ->spa()
            ->databaseNotifications()
            ->brandLogo(asset('/images/logo.svg'))
            ->brandName(config('app.name'))
            ->profile(EditProfile::class)
            ->login(Login::class)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->defaultAvatarProvider(GravatarProvider::class)
            ->widgets([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetTheme::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                //                ThemesPlugin::make(),
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 2,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),

                GravatarPlugin::make()->default('mp')->size(200),
                FilamentApexChartsPlugin::make(),
                FilamentSpatieLaravelBackupPlugin::make()->usingPolingInterval('10s')->noTimeout()->usingQueue('backup'),
            ]);
    }

    public function register(): void
    {
        $metas = [
            '<link rel="apple-touch-icon" sizes="180x180" href="'.asset('/icons/apple-touch-icon.png').'">',
            '<link rel="icon" type="image/png" sizes="32x32" href="'.asset('/icons/favicon-32x32.png').'">',
            '<link rel="icon" type="image/png" sizes="194x194" href="'.asset('/icons/favicon-194x194.png').'">',
            '<link rel="icon" type="image/png" sizes="192x192" href="'.asset(
                '/icons/android-chrome-192x192.png'
            ).'">',
            '<link rel="icon" type="image/png" sizes="16x16" href="'.asset('/icons/favicon-16x16.png').'">',
            '<link rel="manifest" href="'.asset('/icons/site.webmanifest').'">',
            '<link rel="mask-icon" href="'.asset('/icons/safari-pinned-tab.svg').'" color="#000000">',
            '<link rel="shortcut icon" href="'.asset('/icons/favicon.ico').'">',
            '<meta name="msapplication-TileColor" content="#000000">',
            '<meta name="msapplication-TileImage" content="/icons/mstile-144x144.png">',
            '<meta name="msapplication-config" content="/icons/browserconfig.xml">',
            '<meta name="theme-color" content="#000000">',
            '<script>
    function playNotificationSound() {
             let promise = document.querySelector("#notification-sound");
             console.log(promise);
             promise.play();
    }
</script>',
            '<style>
.fi-dropdown-panel {
    max-width: 30em !important;
    width: 28em !important;
}
//.filament-apex-charts-header{
//direction: ltr !important;
//}
.filament-apex-charts-filter-form> div:nth-child(3) {
direction: rtl !important;
width: 40em !important;
left: 0 !important;
right: auto !important;
}

</style>',
        ];

        FilamentView::registerRenderHook(
            'panels::head.start',
            fn (): string => implode("\n", $metas),
        );

        DatabaseNotifications::trigger('filament-notifications.database-notifications-trigger');
        Notifications::alignment(Alignment::Center);
        Notifications::verticalAlignment(VerticalAlignment::Start);

        parent::register();
    }

    public function boot(): void
    {
        TextColumn::macro('jalaliDate', function () {
            $this->formatStateUsing = fn (string $state): string => verta($state)->format('H:i:s - Y/m/d');

            return $this;
        });
        TextEntry::macro('jalaliDate', function () {
            $this->formatStateUsing = fn (string $state): string => verta($state)->format('H:i:s - Y/m/d');

            return $this;
        });
        TextColumn::macro('time', function () {
            $this->formatStateUsing = fn (string $state): string => verta($state)->format('H:i:s');

            return $this;
        });

    }
}
