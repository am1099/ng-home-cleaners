<?php

namespace App\Filament\Resources\Services\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InclusionsRelationManager extends RelationManager
{
    protected static string $relationship = 'inclusions';

    protected static ?string $title = 'Inclusions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('content')
                ->required()
                ->maxLength(255)
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
            ->description('Checklist items shown under “What is included” on this service’s public page.')
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('content')->searchable(),
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
