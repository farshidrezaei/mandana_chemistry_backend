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
        foreach ($tests->whereNotNull('projectTest.started_at')->whereNull('projectTest.finished_at') as $index => $test) {
            $this->handleTestAndReturnMismatchStatus($test, $index);
        }
    }

    private function handleTestAndReturnMismatchStatus(Test $test, int $index): void
    {
        if ($test->projectTest->isExpired()) {
            $test->projectTest->setDone();
        }
    }
}
