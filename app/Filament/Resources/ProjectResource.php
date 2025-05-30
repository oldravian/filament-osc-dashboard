<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Main Content')->schema(
                    [
                        Select::make('categories')
                            ->multiple()
                            ->relationship('categories', 'name'),
                        Select::make('technologies')
                            ->multiple()
                            ->relationship('technologies', 'name'),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('slug', Str::slug($state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('full_name')
                            ->required()
                            ->maxLength(255),
                        // Forms\Components\TextInput::make('keywords')
                        //     ->maxLength(500),
                        Forms\Components\TextInput::make('short_description')
                            ->maxLength(500),
                        Forms\Components\Toggle::make('is_visible')
                            ->required()
                            ->columnSpanFull(),
                        TinyEditor::make('description')->profile('custom')
                            ->setConvertUrls(false)
                            ->maxHeight(600)
                            ->columnSpanFull(),

                    ])->columns(2)->columnSpan(2),

                Section::make('Github Content')->schema(
                    [
                        Forms\Components\TextInput::make('stars')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('git_link')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('demo_link')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('default_branch')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2)->columnSpan(2),

                Section::make('Screenshots')->schema(
                    [
                        FileUpload::make('medias')
                            ->multiple()
                            ->reorderable()
                            ->preserveFilenames()
                            ->directory('images')
                            ->disk('projectsStorage')
                            ->getUploadedFileNameForStorageUsing(function ($file) {
                                return (string) Str::uuid().'.'.$file->getClientOriginalExtension();
                            })
                            ->panelLayout('grid')
                            ->helperText('Upload one or more project media files.'),
                    ]
                )->columnSpanFull()->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category_names')
                    ->label('Categories')
                    ->searchable(false),
                Tables\Columns\TextColumn::make('technology_names')
                    ->label('Technologies')
                    ->searchable(false),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('stars')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('hasDemo')
                    ->label('Has Demo')
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! is_null($record->demo_link)),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('without_demo')
                    ->query(fn (Builder $query) => $query->whereNull('demo_link')),
                SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                SelectFilter::make('technologies')
                    ->relationship('technologies', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    //Tables\Actions\DeleteAction::make(),
                    Action::make('quickEdit')
                        ->label('Quick Edit')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->modalHeading('Quick Edit Project')
                        ->modalSubmitActionLabel('Save')
                        ->requiresConfirmation(false)
                        ->form([
                            Grid::make(2)->schema([
                                Select::make('categories')
                                    ->multiple()
                                    ->preload()
                                    ->relationship('categories', 'name')
                                    ->dehydrated()
                                    ->default(fn (Project $record) => $record->categories->pluck('id')->toArray()),

                                Select::make('technologies')
                                    ->multiple()
                                    ->preload()
                                    ->relationship('technologies', 'name')
                                    ->dehydrated()
                                    ->default(fn (Project $record) => $record->technologies->pluck('id')->toArray()),
                            ]),
                        ])
                        ->action(function (array $data, Project $record): void {
                            $categories = $data['categories'] ?? [];
                            $technologies = $data['technologies'] ?? [];
                            $record->categories()->sync($categories);
                            $record->technologies()->sync($technologies);

                            Notification::make()
                                ->title('Project updated successfully!')
                                ->success()
                                ->send();
                        }),
                ]),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('assignTechnologies')
                        ->label('Assign technologies')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->modalHeading('Assign technologies')
                        ->modalSubmitActionLabel('Save')
                        ->requiresConfirmation(false)
                        ->form([
                            Grid::make(1)->schema([
                                Select::make('technologies')
                                    ->multiple()
                                    ->preload()
                                    ->relationship('technologies', 'name')
                                    ->dehydrated(),
                            ]),
                        ])
                        ->action(function (array $data, Collection $records): void {
                            $technologies = $data['technologies'] ?? [];

                            foreach ($records as $project) {
                                $project->technologies()->sync($technologies);
                            }

                            Notification::make()
                                ->title('Technologies assigned successfully!')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
            'import' => Pages\ImportProject::route('/import'),
        ];
    }
}
