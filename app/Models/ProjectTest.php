<?php

namespace App\Models;

use App\Enums\ProjectStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectTest extends Pivot
{
    protected $table = 'project_test';
    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'is_mismatched' => 'boolean',
        'has_been_notified' => 'boolean',
    ];
    protected $guarded = ['id'];


    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function renewal(): void
    {
        $this->update([
            'renewals_count' => $this->renewals_count + 1,
            'renewals_duration' => $this->renewals_duration + $this->test->duration
        ]);
        redirect("/admin/projects/{$this->project->id}");
    }

    public function getFinishesAt(): ?Carbon
    {
        if ($this->project->isPaused() ||  !$this->isStarted() || $this->isFinished()) {
            return null;
        }
        return $this->started_at
            ->addMinutes($this->test->duration + $this->renewals_duration)
            ->subSeconds($this->passed_duration);
    }

    public function getPassedSeconds(): int
    {
        return (int)$this->started_at->diffInSeconds(now(), true);
    }
    public function getRemainingSeconds(): int
    {
        return ($this->test->duration * 60) - $this->getPassedSeconds() - $this->passed_duration;
    }

    public function isFinished(): bool
    {
        return $this->finished_at !== null;
    }

    public function isStarted(): bool
    {
        return $this->started_at !== null;
    }

    public function isPaused(): bool
    {
        return $this->project->status === ProjectStatusEnum::PAUSED;
    }

    public function isExpired(): bool
    {
        return $this->started_at
            && $this->getFinishesAt()?->isBefore(now())
            && !$this->finished_at;
    }

    public function isMismatched(): bool
    {
        return $this->is_mismatched;
    }


    public function isAbleToRenewal(): bool
    {
        $project = $this->project;
        $remaining = $project->getRemainingMinutes();
        if ($remaining <= 0) {
            return false;
        }
        return $this->test->duration + $project->extra_time <= $remaining;
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

    public function setAsNotified(): void
    {
        $this->update(['has_been_notified' => true]);
    }
}
