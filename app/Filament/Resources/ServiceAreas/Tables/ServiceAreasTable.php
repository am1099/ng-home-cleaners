<?php

namespace App\Filament\Resources\ServiceAreas\Tables;

use App\Filament\Support\ResponsiveRecordTable;
use App\Filament\Support\StandardRecordActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServiceAreasTable
{
    public static function configure(Table $table): Table
    {
        $table = ResponsiveRecordTable::configure(
            $table,
            tableColumns: [
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('postcode_label')->label('Code'),
                TextColumn::make('name')->searchable(),
                ToggleColumn::make('is_active')->label('Active'),
            ],
            cardColumns: [
                ResponsiveRecordTable::stack([
                    Split::make([
                        TextColumn::make('name')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->searchable(),
                        ToggleColumn::make('is_active')->label('Active')->grow(false),
                    ]),
                    TextColumn::make('postcode_label')
                        ->description('Postcode area', position: 'above')
                        ->weight(FontWeight::SemiBold),
                ]),
            ],
        );

        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active'),
            ])
            ->recordActions(StandardRecordActions::make())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
