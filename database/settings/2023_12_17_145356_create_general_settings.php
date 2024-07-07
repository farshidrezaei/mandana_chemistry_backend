<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('general.beforeFinishAlertTime', 15);
        $this->migrator->add('general.forbiddenRenewalTime', 15);
        $this->migrator->add('general.beforeFinishNotifySaleTime', 15);
        $this->migrator->add('general.renewalDurationTime', 15);
    }
};
