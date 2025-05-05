<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected array $uploadedMediaPaths = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function fillForm(): void
    {
        parent::fillForm(); // fills all fields from record

        $this->form->fill([
            ...$this->form->getState(), // ✅ preserve current values
            'medias' => $this->record->medias->pluck('path')->toArray(),
        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->uploadedMediaPaths = collect($data['medias'] ?? [])
            ->map(fn ($path) => ['path' => $path])
            ->toArray();

        unset($data['medias']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Step 1: Extract the new media paths sent from the form
        $newPaths = collect($this->uploadedMediaPaths)
            ->pluck('path')
            ->toArray();

        // Step 2: Get the existing media paths from the database
        $existingPaths = $this->record
            ->medias()
            ->pluck('path')
            ->toArray();

        // Step 3: Determine which paths were removed in the form (i.e. deleted by user)
        $pathsToDelete = array_diff($existingPaths, $newPaths);
        if (! empty($pathsToDelete)) {
            $this->record->medias()
                ->whereIn('path', $pathsToDelete)
                ->delete();
        }

        // Step 4: Determine which paths are newly added (i.e. not yet in the DB)
        $pathsToInsert = array_diff($newPaths, $existingPaths);

        // Step 5: Create media records only for the newly added files
        $newMediaRecords = collect($this->uploadedMediaPaths)
            ->filter(fn ($media) => in_array($media['path'], $pathsToInsert))
            ->values()
            ->toArray();

        if (! empty($newMediaRecords)) {
            $this->record->medias()->createMany($newMediaRecords);
        }
    }
}
