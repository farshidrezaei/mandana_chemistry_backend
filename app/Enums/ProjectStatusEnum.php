<?php

namespace App\Enums;

enum ProjectStatusEnum: string
{
    case PROCESSING = 'processing';
    case PAUSED = 'paused';
}
