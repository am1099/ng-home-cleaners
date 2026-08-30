<?php

namespace App\Filament\Resources\Services\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
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
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('note')
                ->rows(2)
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
                TextColumn::make('task')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('note')
                    ->limit(40)
                    ->wrap()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->toolbarActions([
                CreateAction::make()
                    ->label('Add exclusion')
                    ->modalHeading('Add exclusion')
                    ->modalWidth(Width::Medium)
                    ->createAnother(false)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sort_order'] = (int) ($this->getOwnerRecord()->exclusions()->max('sort_order') ?? 0) + 1;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit exclusion')
                    ->modalWidth(Width::Medium),
                DeleteAction::make(),
            ]);
    }
}
