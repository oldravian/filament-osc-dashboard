<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Main Content')->schema(
                [
                    TextInput::make('title')->required(),
                    Textarea::make('short_description')->label('Meta Description')->columnSpanFull(),
                    RichEditor::make('content')->columnSpanFull()->required(),
                ])->columns(1)->columnSpan(2), //how much equal width columns you want in the form

                Group::make()->schema([
                    Section::make('Featured Image')->description("Suggestion: Upload a 1280x720 image")->schema(
                        [
                            FileUpload::make('featured_image')->disk('public')->directory('blog/files')->columnSpanFull(),
                        ]
                    )->columnSpan(1)->collapsible(),
                    Section::make('Meta')->schema(
                        [
                            Select::make('status')
                            ->options([
                                'Published',
                                'Draft',
                            ]),
                            TextInput::make('tags'),
                        ]
                    )->columnSpan(1),
                ])
                
            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
