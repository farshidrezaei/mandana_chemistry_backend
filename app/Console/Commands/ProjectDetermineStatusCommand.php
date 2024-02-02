<?php

namespace App\Console\Commands;

use App\Models\Test;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProjectDetermineStatusCommand extends Command
{
    protected $signature = 'project:determine-status';

    protected $description = 'Determine Project Status';

    public function handle(): void
    {
        Log::info('Start Schedule');
        Project::query()
            ->with('tests')
            ->whereNull('finished_at')
            ->get()
            ->each(fn (Project $project) => $this->determineProject($project));
    }

    private function determineProject(Project $project): void
    {
        $tests = $project->tests;
        foreach ($tests->whereNotNull('projectTest.started_at')->whereNull('projectTest.finished_at') as $index => $test) {
            $isMismatched = $this->handleTestAndReturnMismatchStatus($test, $index);
            if ($isMismatched) {
                $test->projectTest->project->update([
                    'finished_at' => now()->startOfMinute(),
                    'is_mismatched' => true,
                ]);
                return;
            }
        }
    }

    private function handleTestAndReturnMismatchStatus(Test $test, int $index): bool
    {
        if ($test->projectTest->isExpired()) {
            $test->projectTest->update([
                'finished_at' => $test->projectTest->getFinishesAt(),
                'is_mismatched' => true,
            ]);
            return true;
        }
        return false;
    }
}
