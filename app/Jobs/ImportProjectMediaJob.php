<?php

namespace App\Jobs;

use App\Services\ImageUploaderService;
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
        $project = $this->project;
        $images = $this->images;
        $uploader = new ImageUploaderService();
        $settings = ['disk' => 'projectsStorage', 'directory' => 'images'];
        $medias = [];
        foreach ($images as $key => $image) {
            $ret = $uploader->storeFile($image, $settings, $project->slug . "-" . $key + 1);
            if ($ret !== false) {
                $medias[] = ['path' => $ret['path'], 'is_primary' => $key == 0 ? true : false];
            }
        }

        if (count($medias) > 0) {
            $project->medias()->createMany($medias);
        }
    }
}
