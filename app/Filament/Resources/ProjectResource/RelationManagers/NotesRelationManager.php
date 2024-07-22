<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Note;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';
    protected static ?string $inverseRelationship = 'project';

    protected static ?string $label = 'نوت‌ها';
    protected static ?string $modelLabel = 'نوت‌ها';
    protected static ?string $pluralModelLabel = 'نوت‌ها';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(512),
                SpatieMediaLibraryFileUpload::make('attachment')
                    ->label('پیوست')
                    ->rules([
                        'nullable',
                        'file',
                        'mimes:jpg,jpeg,png,gif,pdf',
                        'max:5100'
                    ])
                    ->collection('note-attachments '),

            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('body')->label('متن'),
                Tables\Columns\TextColumn::make('attachment')->label('پیوست')->formatStateUsing(fn (Note $record) => $record->attachment),
            ])

            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\CreateAction::make(),
//                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
