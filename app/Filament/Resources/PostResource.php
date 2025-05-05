<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Main Content')->schema(
                    [
                        TextInput::make('title')->required(),

                        Select::make('author_id')->required()->label('Author')
                            ->options(
                                \App\Models\User::all()->pluck('name', 'id')
                            ),
                        Textarea::make('short_description')->label('Meta Description')->columnSpanFull(),
                        TinyEditor::make('content')->profile('custom')->fileAttachmentsDisk('public')
                            ->fileAttachmentsVisibility('public')->fileAttachmentsDirectory('blog/files')->setConvertUrls(false)
                            ->maxHeight(600)
                            ->columnSpanFull()->required(),
                    ])->columns(2)->columnSpan(2), //how much equal width columns you want in the form

                Section::make('Featured Image')->description('Suggestion: Upload a 1280x720 image')->schema(
                    [
                        FileUpload::make('featured_image')->disk('public')->directory('blog/files')->columnSpanFull(),
                    ]
                )->columnSpan(1)->collapsible(),
                Section::make('Meta')->schema(
                    [
                        Select::make('status')
                            ->options([
                                'Published' => 'Published',
                                'Draft' => 'Draft',
                            ]),
                        TextInput::make('tags'),
                    ]
                )->columnSpan(1),

            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->toggleable(),
                TextColumn::make('author.name')->label('Author')->toggleable(),
                ImageColumn::make('featured_image')->disk('public')->toggleable(),
                TextColumn::make('created_at')->label('Published At')->date()->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
