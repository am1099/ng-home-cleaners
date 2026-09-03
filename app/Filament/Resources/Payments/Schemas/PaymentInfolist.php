<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Pricing\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payment')
                ->columns(3)
                ->schema([
                    TextEntry::make('paid_date')->date('d M Y'),
                    TextEntry::make('amount_pence')
                        ->label('Amount')
                        ->formatStateUsing(fn (int $state): string => Money::formatPence($state)),
                    TextEntry::make('type')
                        ->badge()
                        ->formatStateUsing(fn (PaymentType $state): string => $state->label()),
                    TextEntry::make('method')
                        ->formatStateUsing(fn (PaymentMethod $state): string => $state->label()),
                    TextEntry::make('booking.reference')
                        ->label('Booking')
                        ->url(fn (Payment $record): ?string => $record->booking_id
                            ? BookingResource::getUrl('view', ['record' => $record->booking_id])
                            : null),
                    TextEntry::make('reference')->placeholder('—'),
                    TextEntry::make('notes')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),

            Section::make('Linked invoices')
                ->schema([
                    RepeatableEntry::make('booking.invoices')
                        ->label('')
                        ->schema([
                            TextEntry::make('invoice_number')
                                ->label('Invoice')
                                ->state(fn (Invoice $record): string => $record->displayNumber())
                                ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record]))
                                ->columnSpan(3),
                            TextEntry::make('status')
                                ->badge()
                                ->formatStateUsing(fn ($state, Invoice $record): string => $record->status?->label() ?? '—')
                                ->color(fn ($state, Invoice $record): string => $record->status?->color() ?? 'gray')
                                ->columnSpan(2),
                            TextEntry::make('total')
                                ->state(fn (Invoice $record): string => $record->totalDisplay())
                                ->columnSpan(2),
                            TextEntry::make('outstanding')
                                ->state(fn (Invoice $record): string => $record->outstandingDisplay())
                                ->columnSpan(2),
                        ])
                        ->columns(9)
                        ->contained(false)
                        ->placeholder('No invoices for this booking.'),
                ])
                ->visible(fn (Payment $record): bool => $record->booking_id !== null),
        ]);
    }
}
