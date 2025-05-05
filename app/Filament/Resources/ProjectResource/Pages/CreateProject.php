<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected array $uploadedMediaPaths = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->uploadedMediaPaths = collect($data['medias'] ?? [])
            ->map(fn ($path) => ['path' => $path])
            ->toArray();

        unset($data['medias']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->medias()->createMany($this->uploadedMediaPaths);
    }
}
