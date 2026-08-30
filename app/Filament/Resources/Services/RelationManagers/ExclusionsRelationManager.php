<?php

namespace App\Filament\Resources\Services\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExclusionsRelationManager extends RelationManager
{
    protected static string $relationship = 'exclusions';

    protected static ?string $title = 'Exclusions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('task')
                ->required()
                ->maxLength(255),
            Textarea::make('note')
                ->rows(2)
                ->columnSpanFull(),
            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('Tasks that are outside the standard checklist for this service. Shown on the public service page under “What is not included”.')
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('task')->searchable(),
                TextColumn::make('note')->limit(40),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
