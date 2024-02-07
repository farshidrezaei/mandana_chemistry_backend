<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class Account extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'md';
    protected static string $view = 'filament-panels::widgets.account-widget';


}
