<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Models\Test;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RenewalAction extends Action
{
    protected function setUp(): void
    {

        parent::setUp();
        $this->label('تمدید')
            ->button()
            ->form(
                fn () => [
                    TextInput::make('body')->label('متن')->required()->maxLength(100),
                ]
            )
            ->action(function (Test $record, array $data) {

                if ($record->projectTest->project->isFinished()) {
                    Notification::make()
                        ->title('.قادر  به انجام این عملیات نیستید')
                        ->danger()
                        ->send();
                } else {
                    if (! $record->projectTest->isAbleToRenewal()) {
                        if (Auth::user()->can('renewal_project_test_project')) {
                            $this->doRenewal($record, $data, true);
                        } else {
                            Notification::make()
                                ->title('.قادر  به انجام این عملیات نیستید')
                                ->danger()
                                ->send();
                        }
                    } else {
                        $this->doRenewal($record, $data);
                    }
                }
            })
            ->requiresConfirmation();
    }

    private function doRenewal(Test $test, array $data, bool $force = false): void
    {
        DB::transaction(function () use ($test) {
            $test->projectTest->renewal();
        });
        activity()
            ->event('renewal')
            ->useLog('projects')
            ->performedOn($test->projectTest->project)
            ->causedBy(Auth::user())
            ->log(" آزمایش  $test->title "
                .' توسط '
                .$test->projectTest->project->user->name
      .' تمدید شد '
            .($force ? 'و باعث افزایش زمان پروژه شد.' : '.').
            'دلیل: '.$data['body']);
        Notification::make()
            ->title('تمدید با موفقیت انجام شد')
            ->success()
            ->send();
        redirect("/admin/projects/{$test->projectTest->project->id}");
    }
}
