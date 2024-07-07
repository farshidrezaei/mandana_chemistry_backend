<?php

namespace App\Tables\Columns;

use Filament\Infolists\Components\Concerns\CanFormatState;
use Filament\Tables\Columns\Column;

class CountDownColumn extends Column
{
    use CanFormatState;

    protected string $view = 'tables.columns.count-down-column';

}
