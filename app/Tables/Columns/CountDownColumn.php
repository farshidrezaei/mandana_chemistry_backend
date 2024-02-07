<?php

namespace App\Tables\Columns;

use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns\CanFormatState;

class CountDownColumn extends Column
{
    use CanFormatState;

    protected string $view = 'tables.columns.count-down-column';

}
