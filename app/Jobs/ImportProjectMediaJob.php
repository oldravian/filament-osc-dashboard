<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Project;

class ImportProjectMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Project $project, public array $images)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger()->info("ImportProjectMediaJob started for project: {$this->project->id}", [
            'project' => $this->project->toArray(),
            'images' => $this->images,
        ]);
    }
}
