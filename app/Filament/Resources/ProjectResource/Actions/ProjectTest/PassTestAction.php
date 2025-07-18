<?php

namespace App\Filament\Resources\ProjectResource\Actions\ProjectTest;

use App\Models\Test;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PassTestAction extends Action
{
    protected function setUp(): void
    {

        parent::setUp();
        $this->label('پاس کردن')
            ->button()
            ->form(
                fn () => [
                    TextInput::make('body')->label('متن')->required()->maxLength(250),
                ]
            )
            ->action(function (Test $record, array $data) {
                if ($record->projectTest->isFinished()) {
                    return;
                }
                $record->projectTest->update([
                    'started_at' => $record->projectTest->started_at ?? now(),
                    'finished_at' => now(),
                    'is_mismatched' => false,
                ]);

                $tests = $record->projectTest->project->tests()->get();

                if ($tests->whereNotNull('projectTest.started_at')->whereNull('projectTest.finished_at')->isEmpty()) {
                    $next = $tests->whereNull('projectTest.started_at')->whereNull('projectTest.started_at')->sortBy('projectTest.order')->first();

                    $next->projectTest->update([
                        'started_at' => now(),
                    ]);
                }

                $this->notify($record, $data);
            })
            ->visible(fn (Test $record) => ! $record->projectTest->isFinished())
            ->requiresConfirmation();
    }

    private function notify(Test $test, array $data): void
    {

        /** @var Collection $users */
        $users = User::role(['admin', 'Sale'])->orWhereIn('id', [$test->projectTest->project->user_id])->get();

        $title = $test->projectTest->project->title ?? $test->projectTest->project->product->title;

        activity()
            ->event('edit')
            ->useLog('projects')
            ->performedOn($test->projectTest->project)
            ->causedBy(Auth::user())
            ->log(' آزمایش '.
                $test->projectTest->test->title.
                " پروژه  $title "
                .' توسط '
                .Auth::user()->name
                .' پاس شد '
                .'دلیل: '
                .$data['body']
            );

        Notification::make()
            ->title(' آزمایش '.
                $test->projectTest->test->title.
                " پروژه  $title "
                .' توسط '
                .Auth::user()->name
                .' پاس شد '
            )
            ->body($data['body'])
            ->actions([
                \Filament\Notifications\Actions\Action::make('showNotifications')->label('مشاهده پروژه')
                    ->button()
                    ->url('/admin/projects/'.$test->projectTest->project->id),
            ])
            ->sendToDatabase($users);

        Notification::make()
            ->title(' آزمایش '.
                $test->projectTest->test->title.
                " پروژه  $title "
                .' توسط '
                .Auth::user()->name
                .' پاس شد '
            )
            ->body($data['body'])
            ->actions([
                \Filament\Notifications\Actions\Action::make('showNotifications')->label('مشاهده پروژه')
                    ->button()
                    ->url('/admin/projects/'.$test->projectTest->project->id),

            ])
            ->broadcast($users);
    }
}
