<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use App\Filament\Support\ResponsiveRecordTable;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        $table = ResponsiveRecordTable::configure(
            $table,
            tableColumns: [
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('key')
                    ->label('Key')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : (string) $state),
                TextColumn::make('subject')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ],
            cardColumns: [
                ResponsiveRecordTable::stack([
                    Split::make([
                        TextColumn::make('name')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->searchable(),
                        TextColumn::make('key')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : (string) $state)
                            ->grow(false),
                    ]),
                    TextColumn::make('subject')
                        ->description('Subject', position: 'above')
                        ->wrap()
                        ->weight(FontWeight::Medium),
                    ResponsiveRecordTable::meta(
                        TextColumn::make('updated_at')->since(),
                    ),
                ]),
            ],
        );

        return $table
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit'),
            ])
            ->toolbarActions([]);
    }
}
