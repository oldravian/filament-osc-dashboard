<?php
namespace App\Services;

use App\Models\Project;
use App\Jobs\ImportProjectMediaJob;
use Exception;
use Illuminate\Support\Str;

class ProjectImportService
{
    public function __construct(
        protected ScraperService $scraper,
        protected HtmlSanitizerService $sanitizer,
    ) {}

    /**
     * @throws Exception
     */
    public function import(array $data): Project
    {
        if (Project::whereGitLinkExists($data['github_link'])) {
            throw new Exception('GitHub link is already added!');
        }

        $repo = $this->normalizeLink($data['github_link']);

        $result = $this->fetchProjectInfo($repo);

        $input = $this->prepareInput($result['project'], $data['github_link']);

        $project = $this->createProject($input, $data['categories'], $data['technologies']);

        $this->dispatchMediaJob($project, $result['images']);

        return $project;
    }

    protected function normalizeLink(string $url): string
    {
        $path = str_replace('https://github.com/', '', trim($url));
        return rtrim($path, '/');
    }

    /**
     * @throws Exception
     */
    protected function fetchProjectInfo(string $repo): array
    {
        try {
            return $this->scraper->getProjectInfo($repo);
        } catch (\Throwable $e) {
            throw new Exception('Error fetching project data!');
        }
    }

    protected function prepareInput(array $project, string $originalLink): array
    {
        // slug
        $slugBase = Str::properSlug($project['title']);
        $project['slug'] = Project::whereSlugExists($originalLink)
            ? Str::properSlug($project['full_name'])
            : $slugBase;

        // trim long fields
        foreach (['short_description', 'keywords'] as $field) {
            if (strlen($project[$field] ?? '') > 500) {
                $project[$field] = substr($project[$field], 0, 490);
            }
        }

        // sanitize HTML
        $project['description'] = $this->sanitizer->addRelAttributes($project['description']);

        return $project;
    }

    protected function createProject(array $input, array $categories, array $technologies): Project
    {
        $project = Project::create($input);
        $project->categories()->attach($categories);
        $project->technologies()->attach($technologies);
        return $project;
    }

    protected function dispatchMediaJob(Project $project, array $images): void
    {
        $images = array_slice($images, 0, 20);
        ImportProjectMediaJob::dispatch($project, $images);
    }
}
