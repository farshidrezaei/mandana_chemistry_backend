<?php

namespace App\Models;

use Carbon\Carbon;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Log;
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

    public function setDone(): void
    {
        $this->update([
            'finished_at' => now()->startOfMinute(),
            'is_mismatched' => false
        ]);

        $unFinishedTests = $this->project
            ->tests()->get()
            ->whereNull('projectTest.finished_at')
            ->sortBy('projectTest.order');
        Log::info((string)$unFinishedTests->count());
        if ($unFinishedTests->isEmpty()) {
            $this->project->update([
                'finished_at' => now()->startOfMinute(),
                'is_mismatched' => false
            ]);
            redirect("/admin/projects/{$this->project->id}");
        } else {
            $unFinishedTests->first()
                ->projectTest
                ->update([
                    'started_at' => now()->startOfMinute(),
                    'is_mismatched' => false
                ]);
        }
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function renewal(): void
    {
        $this->update([
            'renewals_count' => $this->renewals_count + 1,
            'renewals_duration' => $this->renewals_duration + app(GeneralSettings::class)->renewalDurationTime
        ]);
        redirect("/admin/projects/{$this->project->id}");
    }

    public function getFinishesAt(): ?Carbon
    {
        return $this->started_at
            ? $this->started_at->startOfMinute()->addMinutes($this->test->duration + ($this->renewals_duration))
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
            && $this->getFinishesAt()->isBefore(now()->startOfMinute())
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
        return now()->startOfMinute()->diffInMinutes($this->getFinishesAt())
            <= app(GeneralSettings::class)->forbiddenRenewalTime;
    }

    public function isAbleToRenewal(): bool
    {
        return $this->isStarted() && !$this->isFinished() && !$this->isUsedAllRenewals() && !$this->isRenewalTimeHasPassed();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
