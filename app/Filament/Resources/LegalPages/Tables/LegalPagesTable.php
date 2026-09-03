<?php

namespace App\Filament\Resources\LegalPages\Tables;

use App\Filament\Support\ResponsiveRecordTable;
use App\Filament\Support\StandardRecordActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LegalPagesTable
{
    public static function configure(Table $table): Table
    {
        $table = ResponsiveRecordTable::configure(
            $table,
            tableColumns: [
                TextColumn::make('title')->searchable(),
                TextColumn::make('slug'),
                ToggleColumn::make('is_published')->label('Published'),
                TextColumn::make('updated_at')->since()->label('Updated'),
            ],
            cardColumns: [
                ResponsiveRecordTable::stack([
                    Split::make([
                        TextColumn::make('title')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->searchable(),
                        ToggleColumn::make('is_published')->label('Published')->grow(false),
                    ]),
                    ResponsiveRecordTable::meta(
                        TextColumn::make('slug')->description(fn ($record): string => 'Updated '.$record->updated_at?->diffForHumans()),
                    ),
                ]),
            ],
        );

        return $table
            ->defaultSort('slug')
            ->recordActions(StandardRecordActions::make())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
