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
use App\Services\ProjectImportService;
use Exception;

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

    public function importProject(ProjectImportService $importService): void
    {
        try {
            $importService->import($this->form->getState());

            Notification::make()
                ->title('Project imported successfully!')
                ->success()
                ->send();

            $this->form->fill();
        } catch (Exception $e) {
            Notification::make()
                ->title($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
}
