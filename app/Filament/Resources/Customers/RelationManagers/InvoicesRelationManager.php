<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'Invoices';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice number')
                    ->state(fn (Invoice $record): string => $record->displayNumber())
                    ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record])),
                TextColumn::make('booking_reference')
                    ->label('Booking')
                    ->url(fn (Invoice $record): ?string => $record->booking_id
                        ? BookingResource::getUrl('view', ['record' => $record->booking_id])
                        : null)
                    ->placeholder('—'),
                TextColumn::make('issue_date')->date('d M Y')->placeholder('—'),
                TextColumn::make('total')
                    ->state(fn (Invoice $record): string => $record->totalDisplay()),
                TextColumn::make('paid')
                    ->state(fn (Invoice $record): string => $record->paidDisplay()),
                TextColumn::make('outstanding')
                    ->state(fn (Invoice $record): string => $record->outstandingDisplay()),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state, Invoice $record): string => $record->isOverdue()
                        ? 'Overdue'
                        : ($record->status instanceof InvoiceStatus ? $record->status->label() : '—'))
                    ->color(fn ($state, Invoice $record): string => $record->isOverdue()
                        ? 'danger'
                        : ($record->status instanceof InvoiceStatus ? $record->status->color() : 'gray')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
