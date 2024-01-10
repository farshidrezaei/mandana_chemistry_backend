<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public int $beforeFinishAlertTime;
    public int $forbiddenRenewalTime;
    public int $beforeFinishNotifySaleTime;
    public int $renewalDurationTime;

    public static function group(): string
    {
        return 'general';
    }

}
