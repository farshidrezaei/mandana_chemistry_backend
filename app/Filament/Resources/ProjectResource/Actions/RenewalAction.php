<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Test;
use App\Settings\GeneralSettings;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;

class RenewalAction extends Action
{
    protected function setUp(): void
    {

        parent::setUp();
        $this->label('تمدید')
            ->outlined()
            ->form(function () {
                $renewalRangeDuration = (int)app(GeneralSettings::class)->renewalDurationTime;
                $renewalOptions = [];
                for ($i = $renewalRangeDuration; $i <= 120; $i += $renewalRangeDuration) {
                    $renewalOptions[$i] = $i . ' دقیقه ';
                }
                return[
                    Select::make('renewal_duration')->label('مدت تمدید')->options($renewalOptions)->native(false)
                ];
            })
            ->action(fn (Test $record, array $data) => $record->projectTest->renewal($data['renewal_duration']))
            ->requiresConfirmation()
            ->hidden(
                fn (Test $record): bool =>
                !Auth::user()->can('renewal_project_test_project')
                || !$record->projectTest->isAbleToRenewal()
            );
    }
}
