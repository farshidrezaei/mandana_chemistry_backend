<?php

namespace App\Console\Commands;

use App\Models\Test;
use App\Models\Project;
use Illuminate\Console\Command;

class ProjectDetermineStatusCommand extends Command
{
    protected $signature = 'project:determine-status';

    protected $description = 'Determine Project Status';

    public function handle(): void
    {
        Project::query()
            ->with('tests')
            ->whereNull('finished_at')
            ->get()
            ->each(fn (Project $project) => $this->determineProject($project));
    }

    private function determineProject(Project $project): void
    {
        $tests = $project->tests;
        $unFinishedTests = $tests->whereNotNull('projectTest.started_at')->whereNull('projectTest.finished_at');
        if ($unFinishedTests->isNotEmpty()) {
            foreach ($unFinishedTests as $test) {
                $this->handleTestAndReturnMismatchStatus($test);
            }
        } else {
            $tests->filter(fn (Test $test) => $test->projectTest->isMismatched())->isEmpty()
                ? $project->update([
                'finished_at' => now(),
                'is_mismatched' => false
            ])
                : $project->update([
                'finished_at' => now(),
                'is_mismatched' => true
            ]);
        }
    }

    private function handleTestAndReturnMismatchStatus(Test $test): void
    {
        if ($test->projectTest->isExpired()) {
            $test->projectTest->setDone();
        }
    }
}
