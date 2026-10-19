<?php

namespace App\Filament\Resources\BookingConfirmations\Tables;

use App\Enums\BookingConfirmationStatus;
use App\Models\BookingConfirmation;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingConfirmationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('submitted_at')->dateTime('j M Y, H:i')->sortable(),
                TextColumn::make('full_name')->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BookingConfirmationStatus $state): string => $state->label())
                    ->color(fn (BookingConfirmationStatus $state): string => $state->color()),
                TextColumn::make('customer_id')
                    ->label('Customer')
                    ->formatStateUsing(fn ($state, BookingConfirmation $record): string => $record->customer?->fullName() ?? '—'),
                TextColumn::make('booking.reference')->label('Booking')->placeholder('—'),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
