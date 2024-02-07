<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use App\Settings\GeneralSettings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ManageGeneralSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-vertical';
    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?string $title = "تنظیمات کلی";
    protected static string $settings = GeneralSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('beforeFinishAlertTime')
                    ->type('number')
                    ->label('زمان اخطار قبل از پایان آزمایش')
                    ->required(),
                TextInput::make('forbiddenRenewalTime')
                    ->type('number')
                    ->label('زمان از کار افتادن تمدید هر آزمایش')
                    ->required(),
                TextInput::make('beforeFinishNotifySaleTime')
                    ->type('number')
                    ->label('زمان اعلان به فروش قبل از پایان آزمایش')
                    ->required(),
                Select::make('renewalDurationTime')
                    ->label('بازه‌های تمدید زمان هر آزمایش')
                    ->options([
                        15 => "15 دقیقه",
                        30 => "30 دقیقه",
                        60 => "60 دقیقه",
                        120 => "120 دقیقه",
                    ])
                    ->required()->native(false),

            ]);
    }
}
