<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
            ])->orderByPivot('order');
    }

    public function getFinishesAt(): ?Carbon
    {
        $tests = $this->tests->whereNull('projectTest.finished_at')->sortBy('id');
        $first = $tests->first();
        return $first->projectTest->started_at
            ?->addMinutes(
                $tests->sum('duration') + ($tests->sum('projectTest.renewals_duration'))
            );
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
        return $this->started_at && $this->getFinishesAt()->isBefore(now());
    }

    public function isMismatched(): bool
    {
        return $this->is_mismatched;
    }
}
