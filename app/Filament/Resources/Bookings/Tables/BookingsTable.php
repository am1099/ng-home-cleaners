<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Support\StandardRecordActions;
use App\Models\Booking;
use App\Pricing\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('booking_date', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Booking $record): string => BookingResource::getUrl('view', ['record' => $record])),
                TextColumn::make('customer.first_name')
                    ->label('Customer')
                    ->formatStateUsing(fn ($state, Booking $record): string => $record->customer?->fullName() ?? '—')
                    ->searchable(['customers.first_name', 'customers.last_name', 'customers.email']),
                TextColumn::make('service.name')->label('Service')->sortable(),
                TextColumn::make('booking_date')->date('d M Y')->sortable(),
                TextColumn::make('arrival_window')
                    ->label('Arrival')
                    ->formatStateUsing(fn (ArrivalWindow $state): string => $state->shortLabel()),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BookingStatus $state): string => $state->label())
                    ->color(fn (BookingStatus $state): string => $state->color()),
                TextColumn::make('agreed_price_pence')
                    ->label('Agreed')
                    ->formatStateUsing(fn (int $state): string => Money::formatPence($state))
                    ->alignEnd(),
                TextColumn::make('paid')
                    ->label('Paid')
                    ->state(fn (Booking $record): string => $record->paidDisplay())
                    ->alignEnd(),
                TextColumn::make('outstanding')
                    ->label('Outstanding')
                    ->state(fn (Booking $record): string => $record->outstandingDisplay())
                    ->alignEnd(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(BookingStatus::options()),
                SelectFilter::make('service_id')
                    ->label('Service')
                    ->relationship('service', 'name'),
            ])
            ->recordActions(StandardRecordActions::make())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
