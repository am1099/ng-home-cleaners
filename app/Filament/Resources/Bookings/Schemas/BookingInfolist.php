<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use App\Models\Booking;
use App\Pricing\Money;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Booking')
                ->columns(3)
                ->schema([
                    TextEntry::make('reference')->copyable(),
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (BookingStatus $state): string => $state->label())
                        ->color(fn (BookingStatus $state): string => $state->color()),
                    TextEntry::make('service.name')->label('Service'),
                    TextEntry::make('customer.full_name')
                        ->label('Customer')
                        ->state(fn (Booking $record): string => $record->customer?->fullName() ?? '—')
                        ->url(fn (Booking $record): ?string => $record->customer_id
                            ? CustomerResource::getUrl('view', ['record' => $record->customer_id])
                            : null),
                    TextEntry::make('quoteRequest.reference')
                        ->label('Source lead')
                        ->placeholder('—')
                        ->url(fn (Booking $record): ?string => $record->quote_request_id
                            ? QuoteRequestResource::getUrl('view', ['record' => $record->quote_request_id])
                            : null),
                    TextEntry::make('booking_date')->date('l j F Y'),
                    TextEntry::make('arrival_window')
                        ->label('Arrival')
                        ->formatStateUsing(fn (ArrivalWindow $state): string => $state->label()),
                    TextEntry::make('expected_duration_minutes')
                        ->label('Expected duration')
                        ->formatStateUsing(fn (?int $state): string => $state ? $state.' minutes' : '—'),
                    TextEntry::make('full_address')
                        ->label('Address')
                        ->state(fn (Booking $record): string => $record->fullAddress())
                        ->columnSpanFull(),
                    TextEntry::make('internal_notes')
                        ->label('Internal notes')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),

            Section::make('Money')
                ->columns(3)
                ->schema([
                    TextEntry::make('agreed')
                        ->label('Agreed')
                        ->state(fn (Booking $record): string => $record->agreedDisplay()),
                    TextEntry::make('paid')
                        ->label('Paid')
                        ->state(fn (Booking $record): string => $record->paidDisplay()),
                    TextEntry::make('outstanding')
                        ->label('Outstanding')
                        ->state(fn (Booking $record): string => $record->outstandingDisplay())
                        ->color(fn (Booking $record): string => $record->outstandingPence() > 0 ? 'warning' : 'success'),
                    TextEntry::make('overpayment')
                        ->label('Overpayment warning')
                        ->state(fn (Booking $record): string => 'Payments exceed the agreed price by '.Money::formatPence($record->overpaidPence()).'.')
                        ->visible(fn (Booking $record): bool => $record->isOverpaid())
                        ->color('danger')
                        ->columnSpanFull(),
                ]),

            Section::make('Invoice')
                ->columns(3)
                ->schema([
                    TextEntry::make('active_invoice_number')
                        ->label('Invoice')
                        ->state(function (Booking $record): string {
                            $invoice = $record->loadMissing('invoices')->activeInvoice();

                            return $invoice?->displayNumber() ?? 'None yet';
                        })
                        ->url(function (Booking $record): ?string {
                            $invoice = $record->activeInvoice();

                            if (! $invoice) {
                                return null;
                            }

                            return $invoice->isDraft()
                                ? InvoiceResource::getUrl('edit', ['record' => $invoice])
                                : InvoiceResource::getUrl('view', ['record' => $invoice]);
                        }),
                    TextEntry::make('active_invoice_status')
                        ->label('Status')
                        ->badge()
                        ->state(function (Booking $record): string {
                            $invoice = $record->activeInvoice();

                            if (! $invoice) {
                                return 'Not created';
                            }

                            return $invoice->isOverdue()
                                ? 'Overdue'
                                : $invoice->status->label();
                        })
                        ->color(function (Booking $record): string {
                            $invoice = $record->activeInvoice();

                            if (! $invoice) {
                                return 'gray';
                            }

                            return $invoice->isOverdue() ? 'danger' : $invoice->status->color();
                        }),
                    TextEntry::make('active_invoice_total')
                        ->label('Invoice total')
                        ->state(fn (Booking $record): string => $record->activeInvoice()?->totalDisplay() ?? '—'),
                ]),
        ]);
    }
}
