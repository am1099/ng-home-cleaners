<?php

namespace App\Filament\Resources\Services\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
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
                ->label('Item')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->description(null)
            ->paginated(false)
            ->columns([
                TextColumn::make('content')
                    ->label('Item')
                    ->searchable()
                    ->wrap(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->toolbarActions([
                CreateAction::make()
                    ->label('Add inclusion')
                    ->modalHeading('Add inclusion')
                    ->modalWidth(Width::Medium)
                    ->createAnother(false)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sort_order'] = (int) ($this->getOwnerRecord()->inclusions()->max('sort_order') ?? 0) + 1;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit inclusion')
                    ->modalWidth(Width::Medium),
                DeleteAction::make(),
            ]);
    }
}
