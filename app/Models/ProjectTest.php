<?php

namespace App\Models;

use Carbon\Carbon;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTest extends Pivot
{
    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'is_mismatched' => 'boolean'
    ];
    protected $guarded = ['id'];

    private function determineStatus(bool $isMismatched): void
    {
        $this->update([
            'finished_at' => now(),
            'is_mismatched' => $isMismatched
        ]);

        $unFinishedTests = $this->project
            ->tests()
            ->get()
            ->whereNull('projectTest.finished_at')
            ->sortBy('projectTest.order');

        if ($unFinishedTests->isEmpty()) {
            $this->project->update([
                'finished_at' => now(),
                'is_mismatched' => $isMismatched
            ]);
            if (Auth::user()) {
                redirect("/admin/projects/{$this->project->id}");
            }
        } else {
            if ($isMismatched) {
                $this->project->update([
                    'finished_at' => now(),
                    'is_mismatched' => true
                ]);
                if (Auth::user()) {
                    redirect("/admin/projects/{$this->project->id}");
                }
                $unFinishedTests->each(fn (Test $unfinishedTest) => $unfinishedTest
                    ->projectTest
                    ->update([
                        'is_mismatched' => true
                    ]));
            } else {
                $unFinishedTests->first()
                    ->projectTest
                    ->update([
                        'started_at' => now(),
                        'is_mismatched' => false
                    ]);
            }
        }
    }

    public function setDone(): void
    {
        $this->determineStatus(false);
    }

    public function setFailed(): void
    {
        $this->determineStatus(true);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function renewal(int $renewalDuration): void
    {
        $this->update([
            'renewals_count' => $this->renewals_count + 1,
            'renewals_duration' => $this->renewals_duration + $renewalDuration
        ]);
        redirect("/admin/projects/{$this->project->id}");
    }

    public function getFinishesAt(): ?Carbon
    {
        return $this->started_at
            ? $this->started_at->addMinutes($this->test->duration + ($this->renewals_duration))
            : null;
    }

    public function isFinished(): bool
    {
        return $this->finished_at !== null;
    }

    public function isStarted(): bool
    {
        return $this->started_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->started_at
            && $this->getFinishesAt()->isBefore(now())
            && !$this->finished_at;
    }

    public function isMismatched(): bool
    {
        return $this->is_mismatched;
    }

    public function isUsedAllRenewals(): bool
    {
        return $this->renewals_count >= $this->test->renewals_count;
    }

    public function isRenewalTimeHasPassed(): bool
    {
        return now()->diffInMinutes($this->getFinishesAt())
            <= app(GeneralSettings::class)->forbiddenRenewalTime;
    }

    public function isAbleToRenewal(): bool
    {
        return $this->isStarted()
            && !$this->isFinished()
            && !$this->isUsedAllRenewals()
            && !$this->isRenewalTimeHasPassed()
            && !$this->project->isExpired();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getStatusIcon(): string
    {
        if ($this->isFinished()) {
            if ($this->isMismatched()) {
                return 'heroicon-o-x-circle';
            }
            return 'heroicon-o-check-circle';
        } else {
            if ($this->isMismatched()) {
                return 'heroicon-o-x-circle';
            } else {
                if ($this->isStarted()) {
                    return 'heroicon-o-play-circle';
                }
                return 'heroicon-o-clock';
            }
        }
    }

    public function getStatusColor(): string
    {
        if ($this->isFinished()) {
            if ($this->isMismatched()) {
                return 'danger';
            }
            return 'success';
        } else {
            if ($this->isMismatched()) {
                return 'grey';
            } else {
                if ($this->isStarted()) {
                    return 'info';
                }
                return 'warning';
            }
        }
    }
}
