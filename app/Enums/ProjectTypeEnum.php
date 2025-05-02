<?php

namespace App\Enums;

enum ProjectTypeEnum: string
{
    case NORMAL = 'normal';
    case EXTRACTION = 'extraction';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'عادی',
            self::EXTRACTION => 'تخلیه',
        };
    }
}
