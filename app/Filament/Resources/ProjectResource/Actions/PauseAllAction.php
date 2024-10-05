<?php

namespace App\Filament\Resources\ProjectResource\Actions;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PauseAllAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label('توقف همه')
            ->button()
            ->color('warning')
            ->icon('heroicon-o-pause-circle')
            ->form(
                fn () => [
                    TextInput::make('body')->label('متن')->required()->maxLength(100),
                ]
            )
            ->action(function (array $data) {
                Project::query()
                    ->withoutTrashed()
                    ->where(
                        fn (Builder $query) => $query
                            ->where('status', '!=', ProjectStatusEnum::PAUSED)
                            ->orWhereNull('status')
                    )
                    ->whereNull('finished_at')
                    ->get()
                    ->each(fn (Project $project) => $project->pause($data['body']));
                $this->notify($data);
            })
            ->requiresConfirmation()
            ->visible(fn (): bool => Auth::user()->can('pause_all_project_project'));
    }

    private function notify(array $data): void
    {

        $users = User::role(['admin', 'Sale', 'Lab'])->get()->push($user = Auth::user());

        Notification::make()
            ->title("تمام پروژه‌های درحال اجرای واحد آزمایشگاه توسط '{$user->name}' متوقف شدند.")
            ->body($data['body'])
            ->sendToDatabase($users);

        Notification::make()
            ->title("تمام پروژه‌های درحال اجرای واحد آزمایشگاه توسط '{$user->name}' متوقف شدند.")
            ->body($data['body'])
            ->broadcast($users);
    }
}
