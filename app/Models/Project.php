<?php

namespace App\Models;

use App\Enums\ProjectStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Exceptions\InvalidConfiguration;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'status' => ProjectStatusEnum::class,
    ];

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function tests(): BelongsToMany
    {
        return $this->belongsToMany(Test::class)
            ->using(ProjectTest::class)
            ->orderByPivot('order')
            ->as('projectTest')
            ->withPivot([
                'order',
                'started_at',
                'finished_at',
                'is_mismatched',
                'renewals_count',
                'renewals_duration',
                'passed_duration',
            ])->orderByPivot('order');
    }

    /**
     * @throws InvalidConfiguration
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(ActivitylogServiceProvider::determineActivityModel(), 'subject');
    }

    public function isAllTestsFinished(): bool
    {
        return $this->tests->whereNull('projectTest.finished_at')->isEmpty();
    }

    public function getFinishesAt(): ?Carbon
    {
        if ($this->isPaused()) {
            return null;
        }

        $tests = $this->tests
            ->whereNull('projectTest.finished_at')
            ->sortBy('id');
        $first = $tests->shift();
        if ($first === null || ! $first->projectTest->started_at) {
            return null;
        }

        return now()
            ->addSeconds($first->projectTest->getRemainingSeconds())
            ->addMinutes(
                $tests->sum('duration') + ($tests->sum('projectTest.renewals_duration'))
            );
    }

    public function getRemainingMinutes(): ?int
    {
        $finishedAt = $this->getFinishesAt();

        return (int) $finishedAt?->diffInMinutes(now());
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
        return $this->started_at && $this->getFinishesAt()?->isBefore(now());
    }

    public function isPaused(): bool
    {
        return $this->status === ProjectStatusEnum::PAUSED;
    }

    public function addNote(string $body, ?string $attachment): void
    {
        $this->notes()->create([
            'user_id' => Auth::id(),
            'body' => $body,
            'attachment' => $attachment,
        ]);
    }

    private function determineStatus(bool $isMismatched, bool $force = false): void
    {
        DB::transaction(function () use ($isMismatched) {
            $this->update([
                'finished_at' => now(),
                'is_mismatched' => $isMismatched,
            ]);
            $this
                ->tests
                ->whereNull('projectTest.finished_at')
                ->sortBy('projectTest.order')->each(fn (Test $test) => $test->projectTest->update([
                    'finished_at' => now(),
                    'is_mismatched' => $isMismatched,
                ]));
        });

        activity()
            ->event('finished')
            ->useLog('projects')
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->log(
                ' آزمایش '
                .'توسط '
                .$this->user->name
                .' با وضعیت '
                .($isMismatched ? "'نامنطبق'" : "'منطبق'")
                .' تمام شد. '
            );

        redirect("/admin/projects/{$this->id}");
    }

    public function setDone(bool $force = false): void
    {
        $this->determineStatus(false, $force);
    }

    public function setFailed(bool $force = false): void
    {
        $this->determineStatus(true, $force);
    }

    public function pause(string $reason): void
    {
        $this->update(['status' => ProjectStatusEnum::PAUSED]);

        $currentTest = $this->tests
            ->where('projectTest.started_at', '!=', null)
            ->whereNull('projectTest.finished_at')
            ->first();

        $currentTest?->projectTest
            ->increment(
                'passed_duration',
                $currentTest->projectTest->getPassedSeconds()
            );

        activity()
            ->event('paused')
            ->useLog('projects')
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->log(
                ' آزمایش '
                .'توسط '
                .$this->user->name
                .' متوقف شد و این علت برای آن ذکر شد: '
                .$reason
            );
    }

    public function continue(): void
    {
        $currentTest = $this->tests
            ->where('projectTest.started_at', '!=', null)
            ->whereNull('projectTest.finished_at')
            ->first();

        $currentTest?->projectTest->update(['started_at' => now()]);

        $this->update(['status' => ProjectStatusEnum::PROCESSING]);

        activity()
            ->event('continued')
            ->useLog('projects')
            ->performedOn($this)
            ->causedBy(Auth::user())
            ->log(
                ' آزمایش '
                .'توسط '
                .$this->user->name
                .' ادامه داده شد. '
            );

        redirect("/admin/projects/{$this->id}");
    }

    public function calculateTimeBeforeNotify(): int
    {
        $length = $this->tests->sum('duration') + $this->tests->sum('projectTest.renewals_duration');
        if ($length > 60) {
            return 60;
        }

        return (int) ($length / 2);

    }
}
