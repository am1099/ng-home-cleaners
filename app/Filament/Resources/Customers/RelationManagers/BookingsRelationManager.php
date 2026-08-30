<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use App\Pricing\Money;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    protected static ?string $title = 'Bookings';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->url(fn (Booking $record) => BookingResource::getUrl('view', ['record' => $record])),
                TextColumn::make('booking_date')->date('d M Y'),
                TextColumn::make('service.name')->label('Service'),
                TextColumn::make('arrival_window')
                    ->formatStateUsing(fn (ArrivalWindow $state): string => $state->shortLabel()),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BookingStatus $state): string => $state->label())
                    ->color(fn (BookingStatus $state): string => $state->color()),
                TextColumn::make('agreed_price_pence')
                    ->label('Agreed')
                    ->formatStateUsing(fn (int $state): string => Money::formatPence($state)),
                TextColumn::make('outstanding')
                    ->label('Outstanding')
                    ->state(fn (Booking $record): string => $record->outstandingDisplay()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Booking $record) => BookingResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
