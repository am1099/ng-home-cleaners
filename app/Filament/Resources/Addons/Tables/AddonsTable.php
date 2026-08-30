<?php

namespace App\Filament\Resources\Addons\Tables;

use App\Enums\AddonPricingUnit;
use App\Filament\Support\StandardRecordActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AddonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('label')->searchable(),
                TextColumn::make('price_pence')
                    ->label('Guide price')
                    ->formatStateUsing(fn ($record): string => '£'
                        .number_format($record->price_pence / 100, 0)
                        .'–£'
                        .number_format($record->priceMaxPence() / 100, 0)),
                TextColumn::make('pricing_unit')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof AddonPricingUnit
                        ? $state->label()
                        : (string) $state),
                ToggleColumn::make('is_active')->label('Active'),
            ])
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
