<?php

namespace App\Filament\Resources\BookingConfirmations\Schemas;

use App\Enums\BookingConfirmationStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\BookingConfirmation;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingConfirmationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Submission')
                ->columns(2)
                ->schema([
                    TextEntry::make('full_name')->label('Name'),
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (BookingConfirmationStatus $state): string => $state->label())
                        ->color(fn (BookingConfirmationStatus $state): string => $state->color()),
                    TextEntry::make('submitted_at')->dateTime('j M Y, H:i'),
                    TextEntry::make('address')->columnSpanFull(),
                    TextEntry::make('access_instructions')->label('Access')->columnSpanFull(),
                    TextEntry::make('parking')
                        ->formatStateUsing(fn ($state): string => $state?->label() ?? '—'),
                    TextEntry::make('equipment_preference')
                        ->label('Hoover and mop')
                        ->formatStateUsing(fn ($state): string => $state?->label() ?? '—'),
                    TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('valuables_confirmed_at')->label('Valuables secured')->dateTime('j M Y, H:i'),
                    TextEntry::make('payment_confirmed_at')->label('Payment sent')->dateTime('j M Y, H:i'),
                ]),
            Section::make('Matched records')
                ->columns(2)
                ->schema([
                    TextEntry::make('customer_id')
                        ->label('Customer')
                        ->placeholder('No match')
                        ->state(fn (BookingConfirmation $record): string => $record->customer?->fullName() ?? 'No match')
                        ->url(fn (BookingConfirmation $record): ?string => $record->customer_id
                            ? CustomerResource::getUrl('view', ['record' => $record->customer_id])
                            : null),
                    TextEntry::make('booking.reference')
                        ->label('Booking')
                        ->placeholder('No match')
                        ->url(fn (BookingConfirmation $record): ?string => $record->booking_id
                            ? BookingResource::getUrl('view', ['record' => $record->booking_id])
                            : null),
                ]),
        ]);
    }
}
