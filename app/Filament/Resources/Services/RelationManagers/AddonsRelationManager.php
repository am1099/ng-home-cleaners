<?php

namespace App\Filament\Resources\Services\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddonsRelationManager extends RelationManager
{
    protected static string $relationship = 'addons';

    protected static ?string $title = 'Optional add-ons';

    protected static ?string $recordTitleAttribute = 'label';

    public function table(Table $table): Table
    {
        return $table
            ->description('Optional extras customers can add on the estimate form for this service only. Attach an existing add-on from Pricing → Add-ons — this does not create a new product.')
            ->columns([
                TextColumn::make('label')
                    ->label('Customer label')
                    ->searchable()
                    ->description(fn ($record): ?string => $record->name !== $record->label ? $record->name : null),
                TextColumn::make('price_pence')
                    ->label('Price')
                    ->formatStateUsing(fn (int $state): string => '£'.number_format($state / 100, 0)),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach add-on')
                    ->modalHeading('Attach an optional add-on')
                    ->modalDescription('Pick an existing add-on (oven clean, fridge, etc.) to offer with this service. Create new add-ons under Pricing → Add-ons first if the one you need is missing.')
                    ->recordSelectSearchColumns(['label', 'name', 'slug'])
                    ->preloadRecordSelect()
                    ->attachAnother(false),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Remove from service'),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }
}
