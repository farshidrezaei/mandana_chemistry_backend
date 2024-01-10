<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class Account extends Widget
{
    protected static ?int $sort = -3;

    protected int|string|array $columnSpan = 2;
    protected static string $view = 'filament-panels::widgets.account-widget';
}
