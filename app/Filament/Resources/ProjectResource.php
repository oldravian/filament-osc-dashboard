<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('default_branch')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('keywords')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('short_description')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('stars')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('git_link')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('demo_link')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
