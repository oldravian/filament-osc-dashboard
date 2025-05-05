<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use App\Services\ScraperService;
use App\Services\HtmlSanitizerService;
use Illuminate\Support\Str;
use App\Jobs\ImportProjectMediaJob;

class ImportProject extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.resources.project-resource.pages.import-project';

    protected static ?string $title = 'Import Project';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->form->model(Project::make());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('categories')
                    ->label('Categories')
                    ->multiple()
                    ->options(Category::pluck('name', 'id')->toArray()),
                Select::make('technologies')
                    ->label('Technologies')
                    ->multiple()
                    ->options(Technology::pluck('name', 'id')->toArray()),
                TextInput::make('github_link')
                    ->label('GitHub Link')
                    ->required()
                    ->url(),
            ])
            ->statePath('data');
    }

    public function importProject(): void
    {
        $data = $this->form->getState();
        if(Project::whereGitLinkExists($data['github_link'])) {
            Notification::make()
                ->title('GitHub link is already added!')
                ->danger()
                ->send();
            return;
        }

        $git_link = str_replace("https://github.com/", "", $data['github_link']);
        $git_link = rtrim($git_link, '/');

        $scraperService = app(ScraperService::class);
        try{
            $result = $scraperService->getProjectInfo($git_link);
        }
        catch (\Exception $e) {
            Notification::make()
                ->title('Error fetching project data!')
                ->danger()
                ->send();
            return;
        }
        $project_input = $result['project'];

        $candidate_slug = Str::properSlug($project_input['title']);

        // set project slug
        if(Project::whereSlugExists($data['github_link'])){
            $project_input['slug'] = Str::properSlug($project_input['full_name']);
        }
        else{
            $project_input['slug'] = $candidate_slug;
        }

        // set project short_description
        $short_description = $project_input['short_description'];
        if (strlen($short_description) > 500) {
            $project_input['short_description'] = substr($short_description, 0, 490);
        }

        // set project keywords
        $keywords = $project_input['keywords'];
        if (strlen($keywords) > 500) {
            $project_input['keywords'] = substr($keywords, 0, 490);
        }

        // set project description
        $project_input['description'] = HtmlSanitizerService::addRelAttributes($project_input['description']);

        $project = Project::create($project_input);
        $project->categories()->attach($data['categories']);
        $project->technologies()->attach($data['technologies']);

        $images = $result['images'];
        $images = array_slice($images, 0, 20);
        
        //dispatch queue job to import images
        ImportProjectMediaJob::dispatch($project, $images);

        Notification::make()
            ->title('Project imported successfully!')
            ->success()
            ->send();

        // clear form after submit
        $this->form->fill();
    }
}
